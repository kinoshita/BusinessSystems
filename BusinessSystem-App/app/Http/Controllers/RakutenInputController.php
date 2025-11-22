<?php

namespace App\Http\Controllers;

use App\Models\AmazonItem;
use App\Models\ExecuteManage;
use App\Models\ExecuteRakutenManage;
use App\Models\RakutenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RakutenInputController extends Controller
{
    //
    public function index(Request $request)
    {
        $rakuten_data = DB::table('execute_rakuten_manage')
            ->orderBy('id', 'desc')->paginate(10);
        return view('Rakuten.rakutenIndex', compact('rakuten_data'));

    }

    public function create(Request $request)
    {
        $csvFile = $request->file('csvFile');
        $newCsvFileName = $csvFile->getClientOriginalName();
        // ヘッダ、データチェック
        $errors = [];
        $this->setRakutenData($newCsvFileName, $csvFile);
    }

    private function setRakutenData($newCsvFileName, $csvFile)
    {
        $storage_path = "rakuten/csv";
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

        $rakuten_item = new RakutenItem();
        $after_header = collect($rakuten_item->csvExchangeHeader());
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
        $mapping = [
            "注文番号" => "order_id",
            "注文者姓" => "order_last_name",
            "注文者名" => 'order_first_name',
            "送付先郵便番号1" => 'destination_post_code_1',
            "送付先郵便番号2" => 'destination_post_code_2',
            "送付先姓" => 'destination_last_name',
            "送付先名" => 'destination_first_name',
            "送付先住所都道府県" => 'prefectures',
            "送付先住所郡市区" => 'city',
            "送付先住所それ以降の住所" => 'address',
            "送付先電話番号1" => 'telephone_number_1',
            "送付先電話番号2" => 'telephone_number_2',
            "送付先電話番号3" => 'telephone_number_3',
            "個数" => 'quantity',
            "商品名" => 'product_name',
            "単価" => 'unit_price',
            "商品合計金額" => 'total_product_amount'
        ];
        $convertedItems = collect($items)->map(function ($item) use ($mapping) {
            $new = [];

            foreach ($mapping as $jp => $en) {
                // 日本語キーが存在しなければ null
                $new[$en] = $item->get($jp);
            }

            return $new;
        });

//dd($convertedItems);
        $this->setRakuten($convertedItems);
    }

    private function setRakuten($convertedItems)
    {
        try {
            $rakuten_data = DB::transaction(function () use ($convertedItems) {
                $execute_name = "楽天";
                $execute = ExecuteRakutenManage::create([
                    "name" => $execute_name,
                ]);

                foreach ($convertedItems as $item) {

                    //d($item);

                    $type = $this->getType($item["product_name"]);


                    RakutenItem::create([
                        "execute_rakuten_id" => $execute->id,
                        "order_id" => $item["order_id"],
                        'order_last_name' => $item["order_last_name"],
                        'order_first_name' => $item["order_first_name"],
                        'post_code_1' => $item["destination_post_code_1"],
                        'post_code_2' => $item["destination_post_code_2"],
                        'destination_last_name' => $item["destination_last_name"],
                        'destination_first_name' => $item["destination_first_name"],
                        'prefectures' => $item["prefectures"],
                        'city' => $item["city"],
                        'address' => $item["address"],
                        'telephone_number_1' => $item["telephone_number_1"],
                        'telephone_number_2' => $item["telephone_number_2"],
                        'telephone_number_3' => $item["telephone_number_3"],
                        'quantity' => $item["quantity"],
                        'product_name' => $item["product_name"],
                        'unit_price' => $item["unit_price"],
                        'total_product_amount' => $item["total_product_amount"],
                        'content' => $type[2],
                        'file_type' => $type[0],
                        'type' => $type[1],
                    ]);
                }

            });
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 1:クリックポスト
     * 2:レターパック
     * 3:ヤマト宅急便
     *  1:活性炭
     *  2:蒸留水器ケーブル
     *  3:ゴムパッキン
     *  4:オドレミン
     *
     *  10:クエン酸クリーナー
     *  11:蒸留水器ノズル
     *
     *  20:ポリ容器
     *  21:井戸パイプ
     *  22:ガラス容器
     *  30:蒸留水器
     *
     * @param $item
     * @return
     *
     */

    private function getType($item)
    {
        $item = preg_replace('/\s+/u', '', $item);
        if (preg_match('/活性炭\s+パック/u', $item)) {
            return [1, 1, '活性炭パック'];
        } elseif (preg_match('/蒸留水器専用マグネット式電源ケーブル/u', $item)) {
            return [1, 2, '蒸留水器ケーブル'];
        } elseif (preg_match('/ゴムパッキン/u', $item)) {
            return [1, 3, 'ゴムパッキン'];
        } elseif (preg_match('/オドレミン/u', $item)) {
            return [1, 3, 'オドレミン'];
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
        //dd($item);
        return [3, 30, '蒸留水器'];
    }

}
