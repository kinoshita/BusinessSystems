<?php

namespace App\Http\Controllers;

use App\Models\AmazonItem;
use App\Models\ExecuteManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        // ヘッダ、データチェック
        $errors = [];
        $errors = $this->checkCsvHeader($csvFile);

        //dd($errors);
        if (!empty($errors)){
            $errors['file_name'] = $newCsvFileName;
            return back()->with('csv_errors', $errors);
        }

        $this->setAmazonData($newCsvFileName, $csvFile);
        Log:info($csvFile);

    }

    private function setAmazonData($newCsvFileName, $csvFile)
    {
        $csvFile->storeAs('csv', $newCsvFileName);
        $csv = Storage::disk('local')->get("csv/{$newCsvFileName}");
        //$csv = mb_convert_encoding($csv, "UTF-8", "sjis-win");

        //dd($csv);
        $csv = Storage::disk('local')->get("csv/{$newCsvFileName}");

// 文字コードを判定
        $encoding = mb_detect_encoding($csv, ['UTF-8', 'SJIS-win', 'EUC-JP', 'ISO-2022-JP'], true);

// UTF-8に変換
        $csv = mb_convert_encoding($csv, 'UTF-8', $encoding ?: 'SJIS-win');


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

    private function checkCsvHeader($csvFile)
    {
        $newCsvFileName = $csvFile->getClientOriginalName();
        $csvFile->storeAs('public/csv', $newCsvFileName);
        $item = new AmazonItem();
        $csv = Storage::disk('local')->get("public/csv/{$newCsvFileName}");
        //$csv = mb_convert_encoding($csv, "UTF-8", "sjis-win");
        $csv = str_replace(array("\r\n", "\r"), "\n", $csv);
        $uploadedData = collect(explode("\n", mb_convert_encoding($csv, "UTF-8", "auto")));
        $header = collect($item->csvHeader());
        $uploadedHeader = collect(explode(",", $uploadedData->shift()));
        //dd($uploadedHeader, $header);
        $uploadedHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $uploadedHeader[0]);
        $uploadedData = $uploadedData->filter(fn($v) => !empty(trim($v)));
        //
        $errors = [];
        foreach($header as $key=>$value){
            if($header[$key] !== $uploadedHeader[$key]){
                $errors[0] = '入力したcsvファイルのヘッダと、想定しているヘッダが異なっています。';
                return $errors;
            }
        }
        $items = $uploadedData->map(function ($oneRecord) use ($header){
            return $header->combine(collect(explode(",", $oneRecord)));
        });

//dd($items);
        //$check_data = array_map($uploadedData);
        foreach ($items as $i => $row) {
            if ($i === 0) continue; // ヘッダはスキップ
//dd($row, $header);
            if (count($row) !== count($header)) {
                $errors[$i] = '入力した値の項目数が合っておりません。';
                return $errors;
            }
        }
/*
        foreach($items as $index=>$row){
            $rules = [
                    "order-id"  => ['required'],
                    "order-item-id"=> ['required'],
                    "purchase-date"=> ['required'],
                    "payments-date"=> ['required'],
                    "reporting-date" => ['required'],
                    "promise-date" => ['required'],
                    "days-past-promise " => ['required'],
                    "buyer-email" => ['required'],
                    "buyer-name" => ['required'],
                    "buyer-phone-number" => ['required'],
                    "sku" => ['required'],
                    "product-name" => ['required'],
                    "quantity-purchased" => ['required'],
                    "quantity-shipped" => ['required'],
                    "quantity-to-ship" => ['required'],
                    "ship-service-level" => ['required'],
                    "recipient-name" => ['required'],
                    "ship-address-1" => ['required'],
                    "ship-address-2" => ['required'],
                    "ship-address-3" => ['required'],
                    "ship-city" => ['required'],
                    "ship-state" => ['required'],
                    "ship-postal-code" => ['required'],
                    "ship-country" => ['required'],
                    "payment-method" => ['required'],
                    "cod-collectible-amount" => ['required'],
                    "already-paid" => ['required'],
                    "payment-method-fee" => ['required'],
                    "scheduled-delivery-start-date" => ['required'],
                    "scheduled-delivery-end-date" => ['required'],
                    "points-granted" => ['required'],
                    "is-prime" => ['required'],
                    "verge-of-cancellation" => ['required'],
                    "verge-of-lateShipment" => ['required']
            ];
            $validator = Validator::make($row->toArray(), $rules, ['message']);
            if ($validator->fails()) {
                $errors[$index] = $validator->errors()->all();
            }

        }
*/


        return $errors;
    }


}
