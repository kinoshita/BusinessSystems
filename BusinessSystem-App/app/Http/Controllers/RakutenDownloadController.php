<?php

namespace App\Http\Controllers;

use App\Exports\RakutenExport;
use App\Exports\RakutenLetterExport;
use App\Models\ClickPost;
use App\Models\LetterPack;
use App\Models\RakutenItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RakutenDownloadController extends Controller
{
    //
    public function download(Request $request)
    {
        $rakuten_id = $request->get('rakuten_id');
        $this->getBaseFileForRakuten($rakuten_id);
        $this->getClickPost($rakuten_id);
        $this->getLetterPack($rakuten_id);
        $this->getExcelRakuten($rakuten_id);
        $this->getLetterPackPrintExcel($rakuten_id);

    }

    private function getBaseFileForRakuten($rakuten_id)
    {
        $query = DB::table('rakuten_data')
            ->select(['rakuten_data.*'])
            ->where('execute_rakuten_id', $rakuten_id)
            ->orderBy('type')
            ->get();

        $header = new RakutenItem();
        $csvHeader = $header->csvExchangeHeader();
        $csvData = $query;

        $csvFileName = "楽天全出力リスト";
        $csvPath = storage_path("app/private/files/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($csvData as $row) {
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);

            $product_name = preg_replace('/マグネット式電源コードタイプ ピュアポット /', '', $row->product_name);

            $row_data = [
                $row->order_id,
                $row->order_last_name,
                $row->order_first_name,
                $row->post_code_1,
                $row->post_code_2,
                $row->destination_last_name,
                $row->destination_first_name,
                $row->prefectures,
                $row->city,
                $row->address,
                $row->telephone_number_1,
                $row->telephone_number_2,
                $row->telephone_number_3,
                $row->quantity,
                $row->product_name,
                $row->unit_price,
                $row->total_product_amount,
            ];
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }


    private function getLetterPackPrintExcel($id)
    {
        $data = RakutenItem::where('execute_rakuten_id', $id)->first();
        $query = DB::table('rakuten_data')
            ->select('rakuten_data.*')
            ->where('execute_rakuten_id', $id)
            ->where('file_type', '2')
            ->orderBy('id')
            ->get();

        $output_name = '楽天レターパック(印刷用)';
        Excel::store(
            new RakutenLetterExport($query), "files/{$output_name}.xlsx"
        );

    }

    private function getClickPost($id)
    {
        $query = DB::table("rakuten_data")
            ->select("rakuten_data.*")
            ->where("execute_rakuten_id", "=", $id)
            ->where('file_type', '1')
            ->orderBy('product_name')
            ->get();

        $header = new ClickPost();
        $csvHeader = $header->csvHeader();
        $csvData = $query;

        $csvFileName = "楽天クリックポスト";
        $csvPath = storage_path("app/private/files/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];


        foreach ($csvData as $row) {
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);
            $row_data = [
                $row->post_code_1 . '-' . $row->post_code_2,
                $row->destination_last_name . ' ' . $row->destination_first_name,
                '様',
                $row->prefectures,
                $row->city,
                $row->address,
                '',
                $row->content,
            ];
            //Log::info("Click post");
            //Log::info(print_r($row_data,true));
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    private function getLetterPack($id)
    {
        $query = DB::table("rakuten_data")
            ->select("rakuten_data.*")
            ->where("execute_rakuten_id", "=", $id)
            ->where('file_type', '2')
            ->get();

        // ヘッダ取得
        $header = new LetterPack();
        $csvHeader = $header->csvHeader();
        $csvData = $query;
        $csvFileName = "楽天用レターパック";
        $csvPath = storage_path("app/private/files/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($csvData as $row) {
            $row_data = [
                $row->post_code_1 . '-' . $row->post_code_2,
                $row->destination_last_name . ' ' . $row->destination_first_name,
                '様',
                $row->prefectures,
                $row->city,
                $row->address,
                '',
                $row->telephone_number_1 . $row->telephone_number_2 . $row->telephone_number_3,
            ];
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    private function getExcelRakuten($id)
    {
        $data = RakutenItem::where('execute_rakuten_id', $id)->first();
        $query = DB::table("rakuten_data")
            ->select("rakuten_data.*")
            ->where("execute_rakuten_id", "=", $id)
            ->orderByRaw('CAST(type AS UNSIGNED) ASC')
            ->get();
        $output_name = '出荷リスト(楽天)';
        Excel::store(
            new RakutenExport($query), "files/{$output_name}.xlsx"
        );

    }


    private function convertEncoding($array)
    {
        return array_map(function ($value) {
            return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
        }, $array);
    }
}
