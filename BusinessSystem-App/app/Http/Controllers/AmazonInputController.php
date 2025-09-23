<?php

namespace App\Http\Controllers;

use App\Models\AmazonItem;
use App\Models\ExecuteManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AmazonInputController extends Controller
{
    //
    public function index()
    {
        Log::info("index");

        $amazon_data = DB::table('execute_manage_table')
        ->orderBy('id')->paginate(10);





        return view('Amazon.amazonIndex', compact('amazon_data'));
    }

    public function create(Request $request)
    {
        Log::info('create');
        $csvFile = $request->file('csvFile');

        $newCsvFileName = $csvFile->getClientOriginalName();
        $this->setAmazonData($newCsvFileName, $csvFile);
        Log:info($csvFile);





    }

    private function setAmazonData($newCsvFileName, $csvFile)
    {
        $csvFile->storeAs('csv', $newCsvFileName);
        $csv = Storage::disk('local')->get("csv/{$newCsvFileName}");
        //$csv = mb_convert_encoding($csv, "UTF-8", "sjis-win");

        //dd($csv);



        $csv = str_replace(array("\r\n", "\r"), "\n", $csv);
        $uploadedData = collect(explode("\n", mb_convert_encoding($csv, "UTF-8", "auto")));
        // テーブルとCSVファイルのヘッダーの比較
        //$header = collect($item->csvHeader());
        $uploadedHeader = collect(explode(",", $uploadedData->shift()));
        $uploadedHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $uploadedHeader[0]);
        Log::info("uploadedHeader");
        Log::info($uploadedHeader);


        $amazon_item = new AmazonItem();
        $after_header = collect($amazon_item->csvExchangeHeader2());
//dd($after_header);
        $indexes = [];
        // データ部分を整形
        $uploadedData = $uploadedData->filter(fn($v) => !empty(trim($v)));
//dd($uploadedData);
// 並び替え + 不要列削除
        $items = $uploadedData->map(function ($oneRecord) use ($after_header, $uploadedHeader) {
            $record = collect(explode(",", $oneRecord));
//dd($record, $uploadedHeader);
            // 「アップロードCSVのヘッダー」=>「データ」のペアにする
            $assoc = $uploadedHeader->combine($record);

            // 必要なヘッダーだけ抽出し、定義された順に並べる
            return $after_header->mapWithKeys(fn($h) => [$h => $assoc->get($h)]);
        });
        /*
        $items = $uploadedData->map(function ($oneRecord) use ($indexes) {




            return $indexes->combine(collect(explode(",", $oneRecord)));
        });
        */
        //dd($items);

        $this->setAmazon($items);



    }

    private function setAmazon($items)
    {
        try{
            $amazon_data = DB::transaction(function() use($items){
                Log::info("uploadedHeader");

                $execute_name = "Amazon";
                $execute = ExecuteManage::create([
                    "name" => $execute_name
                ]);

                foreach ($items as $item){
                    $type = $this->getType($item["product-name"]);
                    AmazonItem::create([
                        'buyer-name' => $item["buyer-name"],
                        'execute_id' => $execute->id,
                        'ship-postal-code' => $item["ship-postal-code"],
                        'recipient-name' => $item["recipient-name"],
                        'ship-state' => $item["ship-state"],
                        'ship-address-1' => $item["ship-address-1"],
                        'ship-address-2' => $item["ship-address-2"],
                        'ship-address-3' => $item["ship-address-3"],
                        '内容品' => $item["内容品"] ?? $type[1],
                        'quantity-to-ship' => $item["quantity-to-ship"],
                        'product-name' => $item["product-name"],
                        'type' => $type[0]
                    ]);
                }


            });
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    private function getType($item)
    {
        if(strpos($item, '活性炭')){
            return [1, '活性炭パック'];
        }elseif (strpos($item, 'ゴムパッキン')){
            return [2, 'ゴムパッキン'];
        }elseif (strpos($item, 'パイプ')) {
            return [3, 'パイプ'];
        }elseif (strpos($item, 'クリーナー')){
            return [4, 'クリーナー'];
        }else{
            return [9, ''];
        }
        //return 9;
    }

}
