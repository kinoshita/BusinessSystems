<?php

namespace App\Http\Controllers;

use App\Models\AmazonItem;
use App\Models\AmazonYamatoItem;
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
        return view('Amazon.amazonFinish');
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
        Log::info("==== updated Data ====");
        Log::info($uploadedData);

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

        // yamato用データのみ
        $yamato_items = $uploadedData->map(function ($oneRecord) use ($uploadedHeader) {
             $record = collect(explode(",", $oneRecord));
             $assoc = $uploadedHeader->combine($record);
             return $assoc;
        });

        Log::info("====- Yamato Items ==== ");
        Log::info($yamato_items);

        $this->setAmazon($items, $yamato_items);




    }

    private function setAmazon($items, $yamato_items)
    {
        try{
            $amazon_data = DB::transaction(function() use($items, $yamato_items){
                Log::info("uploadedHeader");

                $execute_name = "Amazon";
                $execute = ExecuteManage::create([
                    "name" => $execute_name
                ]);

                foreach ($items as $item){
                    $type = $this->getType($item["product-name"]);
                    AmazonItem::create([
                        'execute_id' => $execute->id,
                        'buyer-name' => $item["buyer-name"],
                        'buyer-phone-number' => $item["buyer-phone-number"],
                        'ship-postal-code' => $item["ship-postal-code"],
                        'recipient-name' => $item["recipient-name"],
                        'ship-state' => $item["ship-state"],
                        'ship-address-1' => $item["ship-address-1"],
                        'ship-address-2' => $item["ship-address-2"],
                        'ship-address-3' => $item["ship-address-3"],
                        '内容品' => $item["内容品"] ?? $type[2],
                        'quantity-to-ship' => $item["quantity-to-ship"],
                        'product-name' => $item["product-name"],
                        'type' => $type[1],
                        'file_type' => $type[0],
                    ]);
                }
                foreach($yamato_items as $y_item){
                    $type = $this->getType($y_item["product-name"]);
                    if($type[0] == '3'){
                        // yamato分のみデータ設定する
                        $this->setAmazonYamato($execute->id, $y_item);
                    }
                }



            });
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
    /**
     *
     * @param $item
     * @return void
     *
     *
     */
    private function setAmazonYamato($execute_id, $item)
    {
        AmazonYamatoItem::create([
            'execute_id' => $execute_id,
            "order-id" => $item["order-id"],
            "order-item-id" => $item["order-item-id"],
            "purchase-date" => $item["purchase-date"],
            "payments-date" => $item["payments-date"],
            "reporting-date" => $item["reporting-date"],
            "promise-date" => $item["promise-date"],
            "days-past-promise" => $item["days-past-promise"],
            "buyer-email" => $item["buyer-email"],
            "buyer-name" => $item["buyer-name"],


            "buyer-phone-number" => $item["buyer-phone-number"],
            "sku" => $item["sku"],
            "product-name" => $item["product-name"],
            "quantity-purchased" => $item["quantity-purchased"],
            "quantity-shipped" => $item["quantity-shipped"],
            "quantity-to-ship" => $item["quantity-to-ship"],
            "ship-service-level" => $item["ship-service-level"],
            "recipient-name" => $item["recipient-name"],
            "ship-address-1" => $item["ship-address-1"],
            "ship-address-2" => $item["ship-address-2"],
            "ship-address-3" => $item["ship-address-3"],
            "ship-city" => $item["ship-city"],
            "ship-state" => $item["ship-state"],
            "ship-postal-code" => $item["ship-postal-code"],
            "ship-country" => $item["ship-country"],
            "payment-method" => $item["payment-method"],
            "cod-collectible-amount" => $item["cod-collectible-amount"],
            "already-paid" => $item["already-paid"],
            "payment-method-fee" => $item["payment-method-fee"],
            "scheduled-delivery-start-date" => $item["scheduled-delivery-start-date"],
            "scheduled-delivery-end-date" => $item["scheduled-delivery-end-date"],
            "points-granted" => $item["points-granted"],
            "is-prime" => $item["is-prime"],
            "verge-of-cancellation" => $item["verge-of-cancellation"],
            "verge-of-lateShipment" => $item["verge-of-lateShipment"],
        ]);
    }





    /**
     *
     *
     * @param $item
     * @return array
     * 1: 活性炭パック
     * 2: 蒸留水器ケーブル
     * 3: ゴムパッキン
     *
     * 10: クエン酸クリーナー
     * 11: 蒸留水器ノズル
     * 12:
     *
     *
     * 20: ポリ容器
     * 21: 井戸パイプ
     * 22: ガラス容器
     * 30: 蒸留水器
     *
     *  1: クリックポスト
     *  2: レターパック
     *  3: ヤマト宅急便
     *
     *  変更後の商品名
     * 　活性炭　パック　=>　活性炭パック
     *   電源ケーブル　 =>  蒸留水器ケーブル
     *   ゴムパッキン  => ゴムパッキン
     *
     *  クエン酸クリーナー => クエン酸クリーナー
     *  蒸留水器　専用ノズル => 蒸留水器ノズル
     *
     * 　ポリ容器 => ポリ容器
     *   井戸　パイプ　=> 井戸パイプ
     * 　ガラス容器　=> ガラス容器
     *
     *
     *
     *
     */
    private function getType($item)
    {

        if (preg_match('/活性炭\s+パック/u', $item)) {
            return [1, 1, '活性炭パック'];
        }elseif(preg_match('/電源ケーブル/u', $item)){
            return [1, 2, '蒸留水器ケーブル'];
        }elseif(preg_match('/ゴムパッキン/u', $item)){
            return [1, 3, 'ゴムパッキン'];
        }elseif(preg_match('/クエン酸/u', $item)){
            return [2, 10, 'クエン酸クリーナー'];
        }elseif(preg_match('/蒸留水器[\s+]*専用ノズル/u', $item)){
            return [2, 11, '蒸留水器ノズル'];
        }elseif(preg_match('/ポリ容器/u', $item)){
            return [3, 20, 'ポリ容器'];
        }elseif(preg_match('/井戸.*パイプ/u', $item)){
            return [3, 21, '井戸パイプ'];
        }elseif(preg_match('/ガラス容器[\s　]*白/u', $item)){
            return [3, 22, 'ガラス容器'];
        }elseif(preg_match('/ガラス容器[\s　]*黒/u', $item)){
            return [3, 22, 'ガラス容器'];
        }elseif(preg_match('/ステンレスボディ/u', $item) || preg_match('/スチールボディ/u', $item)){
            //dd("dddd");
            return [3, 30, '蒸留水器'];
        }
        return [3, 30, '蒸留水器'];


/*

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
*/
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
