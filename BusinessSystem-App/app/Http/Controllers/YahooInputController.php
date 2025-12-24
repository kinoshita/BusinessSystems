<?php

namespace App\Http\Controllers;

use App\Models\ExecuteYahooManage;
use App\Models\RakutenItem;
use App\Models\YahooItem;
use App\Models\YahooItemDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YahooInputController extends Controller
{
    //
    public function index()
    {
        $yahoo_data = DB::table('execute_yahoo_manage')
        ->orderBy('id', 'desc')->paginate(10);
        return view('Yahoo.yahooIndex', ['yahoo_data' => $yahoo_data]);
    }

    public function create(Request $request)
    {
        $csvFileItem = $request->file('yahooItem');
        $newCsvFileItemName = $csvFileItem->getClientOriginalName();
        // ヘッダ、データチェック

        $csvFileItemDetail = $request->file('yahooItemDetail');
        $newCsvFileItemDetailName = $csvFileItemDetail->getClientOriginalName();
        $errors = [];
        $this->setYahooData($newCsvFileItemName, $csvFileItem, $newCsvFileItemDetailName, $csvFileItemDetail);
        return view('Yahoo.yahooFinish');
    }

    private function setYahooData($newCsvFileItemName, $csvFileItem, $newCsvFileItemDetailName, $csvFileItemDetail)
    {

        $csvItem = $this->setYahooItem($newCsvFileItemName, $csvFileItem);
        $csvItemDetail = $this->setYahooItemDetail($newCsvFileItemDetailName, $csvFileItemDetail);
        //dd($csvItem, $csvItemDetail);

        $this->setYahoo($csvItem, $csvItemDetail);
//dd($convertedItems);
       // $this->setRakuten($convertedItems);
    }

    private function setYahooItem($newCsvFileItemName, $csvFileItem)
    {
        $storage_path = "yahoo/csv";
        $csvFileItem->storeAs('csv', $newCsvFileItemName);
        $csvItem = Storage::disk('local')->get("csv/{$newCsvFileItemName}");
        //$csv = mb_convert_encoding($csv, "UTF-8", "sjis-win");

        //dd($csv);


// 文字コードを判定
        $encoding = mb_detect_encoding($csvItem, ['UTF-8', 'SJIS-win', 'EUC-JP', 'ISO-2022-JP'], true);

// UTF-8に変換
        $csvItem = mb_convert_encoding($csvItem, 'UTF-8', $encoding ?: 'SJIS-win');


        $csvItem = str_replace(array("\r\n", "\r"), "\n", $csvItem);
        $uploadedData = collect(explode("\n", mb_convert_encoding($csvItem, "UTF-8", "auto")));
        // テーブルとCSVファイルのヘッダーの比較
        //$header = collect($item->csvHeader());

        $uploadedHeader = collect(explode(",", $uploadedData->shift()));
        $uploadedHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $uploadedHeader[0]);
        Log::info("uploadedHeader");
        Log::info($uploadedHeader);
        Log::info("==== updated Data ====");
        Log::info($uploadedData);

        $yahoo_item = new YahooItem();
        $after_header = collect($yahoo_item->csvExchangeHeader());
//dd($after_header);
        $indexes = [];
        // データ部分を整形
        $uploadedData = $uploadedData->filter(fn($v) => !empty(trim($v)));
//dd($uploadedData);
// 並び替え + 不要列削除
        $items = $uploadedData->map(function ($oneRecord) use ($after_header, $uploadedHeader) {
            $columns = str_getcsv($oneRecord);
            // 前後の不可視文字を削除（全角スペース含む）
            //  $columns = array_map(fn($v) => trim($v, " \t\n\r\0\x0B　"), $columns);
            $record = collect($columns);

//dd($record, $uploadedHeader);
            // 「アップロードCSVのヘッダー」=>「データ」のペアにする
            // ヘッダ側も同じく trim する
            $uploadedHeader = $uploadedHeader->map(function ($h) {
                $h = trim($h);
                $h = preg_replace('/^"(.*)"$/u', '$1', $h);
                return $h;
            });
            $assoc = $uploadedHeader->combine($record);
//dd($uploadedHeader, $after_header);
            // 必要なヘッダーだけ抽出し、定義された順に並べる
            return $after_header->mapWithKeys(fn($h) => [$h => $assoc->get($h)]);
        });
        return $items;
    }

    private function setYahooItemDetail($newCsvFileItemDetailName, $csvFileItemDetail)
    {
        $storage_path = "yahoo/csv";
        //$csv = mb_convert_encoding($csv, "UTF-8", "sjis-win");
        $csvFileItemDetail->storeAs('csv', $newCsvFileItemDetailName);
        $csvItemDetail = Storage::disk('local')->get("csv/{$newCsvFileItemDetailName}");


        //dd($csv);


// 文字コードを判定
        $encoding = mb_detect_encoding($csvItemDetail, ['UTF-8', 'SJIS-win', 'EUC-JP', 'ISO-2022-JP'], true);

// UTF-8に変換
        $csvItemDetail = mb_convert_encoding($csvItemDetail, 'UTF-8', $encoding ?: 'SJIS-win');


        $csvItemDetail = str_replace(array("\r\n", "\r"), "\n", $csvItemDetail);
        $uploadedData = collect(explode("\n", mb_convert_encoding($csvItemDetail, "UTF-8", "auto")));
        // テーブルとCSVファイルのヘッダーの比較
        //$header = collect($item->csvHeader());

        $uploadedHeader = collect(explode(",", $uploadedData->shift()));
        $uploadedHeader[0] = preg_replace('/^\xEF\xBB\xBF/', '', $uploadedHeader[0]);
        Log::info("uploadedHeader");
        Log::info($uploadedHeader);
        Log::info("==== updated Data ====");
        Log::info($uploadedData);

        $yahoo_item = new YahooItemDetail();
        $after_header = collect($yahoo_item->csvExchangeHeader());
//dd($after_header);
        $indexes = [];
        // データ部分を整形
        $uploadedData = $uploadedData->filter(fn($v) => !empty(trim($v)));
//dd($uploadedData);
// 並び替え + 不要列削除
        $items = $uploadedData->map(function ($oneRecord) use ($after_header, $uploadedHeader) {
            $columns = str_getcsv($oneRecord);
            // 前後の不可視文字を削除（全角スペース含む）
            //  $columns = array_map(fn($v) => trim($v, " \t\n\r\0\x0B　"), $columns);
            $record = collect($columns);

//dd($record, $uploadedHeader);
            // 「アップロードCSVのヘッダー」=>「データ」のペアにする
            // ヘッダ側も同じく trim する
            $uploadedHeader = $uploadedHeader->map(function ($h) {
                $h = trim($h);
                $h = preg_replace('/^"(.*)"$/u', '$1', $h);
                return $h;
            });
            $assoc = $uploadedHeader->combine($record);
//dd($uploadedHeader, $after_header);
            // 必要なヘッダーだけ抽出し、定義された順に並べる
            return $after_header->mapWithKeys(fn($h) => [$h => $assoc->get($h)]);
        });
        return $items;
    }

    private function setYahoo($items, $itemDetails)
    {
        try{
            $execute_name = "Yahoo";
            $execute = ExecuteYahooManage::create([
                "name" => $execute_name,
            ]);
            foreach($items as $item){
                if (!str_starts_with($item['ShipPhoneNumber'], '0')){
                    $item['ShipPhoneNumber'] = '0'.$item['ShipPhoneNumber'];
                }



                YahooItem::create([
                    'execute_yahoo_id' => $execute->id,
                    'OrderId' => $item['OrderId'],
                    'BillName' => $item['BillName'],
                    'ShipZipCode' => $item['ShipZipCode'],
                    'ShipName' => $item['ShipName'],
                    'ShipPrefecture' => $item['ShipPrefecture'],
                    'ShipCity' => $item['ShipCity'],
                    'ShipAddress1' => $item['ShipAddress1'],
                    'ShipAddress2' => $item['ShipAddress2'],
                    'ShipSection1' => $item['ShipSection1'] ?? '',
                    'ShipSection2' => $item['ShipSection2'] ?? '',
                    'ShipPhoneNumber' => $item['ShipPhoneNumber'],
                    'QuantityDetail' => $item['QuantityDetail'],
                    'BillMailAddress' => $item['BillMailAddress'],

                ]);
            }

            foreach($itemDetails as $itemDetail){
                $type = $this->getType($itemDetail['Title']);
                YahooItemDetail::create([
                    'execute_yahoo_id' => $execute->id,
                    'OrderId' => $itemDetail['OrderId'],
                    'LineId' => $itemDetail['LineId'],
                    'ItemId' => $itemDetail['ItemId'],
                    'Title' => $itemDetail['Title'],
                    'SubCode' => $itemDetail['SubCode'],
                    'Quantity' => $itemDetail['Quantity'],
                    'content' => $type[2],
                    'file_type' => $type[0],
                    'type' => $type[1],
                ]);

            }




        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    private function getType($item)
    {
        if (preg_match('/活性炭\s+パック/u', $item)) {
            return [1, 1, '活性炭パック'];
        } elseif (preg_match('/電源ケーブル/u', $item)) {
            return [1, 2, '蒸留水器ケーブル'];
        } elseif (preg_match('/ゴムパッキン/u', $item)) {
            return [1, 3, 'ゴムパッキン'];
        } elseif (preg_match('/クエン酸/u', $item)) {
            return [2, 10, 'クエン酸クリーナー'];
        } elseif (preg_match('/蒸留水器[\s+]*専用ノズル/u', $item)) {
            return [2, 11, '蒸留水器ノズル'];
        } elseif (preg_match('/ポリ容器/u', $item)) {
            return [3, 20, 'ポリ容器'];
        } elseif (preg_match('/井戸.*パイプ/u', $item)) {
            return [3, 21, '井戸パイプ'];
        } elseif (preg_match('/ガラス容器[\s　]*白/u', $item)) {
            return [3, 22, 'ガラス容器'];
        } elseif (preg_match('/ガラス容器[\s　]*黒/u', $item)) {
            return [3, 22, 'ガラス容器'];
        } elseif (preg_match('/ステンレスボディ/u', $item) || preg_match('/スチールボディ/u', $item)) {
            //dd("dddd");
            return [3, 30, '蒸留水器'];
        }
        return [3, 30, '蒸留水器'];
    }

}
