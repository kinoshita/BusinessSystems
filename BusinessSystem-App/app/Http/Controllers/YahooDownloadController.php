<?php

namespace App\Http\Controllers;

use App\Exports\RakutenExport;
use App\Exports\YahooExport;
use App\Exports\YahooLetterExport;
use App\Models\ClickPost;
use App\Models\ExecuteYahooManage;
use App\Models\LetterPack;
use App\Models\RakutenItem;
use App\Models\YahooItem;
use App\Models\YahooItemDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipStream\ZipStream;

class YahooDownloadController extends Controller
{
    //
    public function download(Request $request)
    {
        $yahoo_id = $request->input('yahoo_id');
        $manage = new ExecuteYahooManage();
        $directory = $manage->getYahooBaseDirectory();
        $files = Storage::disk('local')->files('files/yahoo');

        //dd($files);
        Storage::disk('local')->delete($files);
        // 全出力リスト
        $this->setBaseFile($yahoo_id);
        // 加工シート
        $this->setProcessingWork($yahoo_id);
        // クリックポスト
        $this->getClickPost($yahoo_id);
        // レターパック
        $this->getLetterPack($yahoo_id);
        //
        $this->getExcelYahoo($yahoo_id);
        $this->getLetterPackPrintExcel($yahoo_id);
        $this->downloadYahooZip();
    }

    private function getLetterPackPrintExcel($yahoo_id)
    {
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query)  use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id)
                    ->where('file_type', '2');
            }])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        $output_name = 'Yahooレターパック(印刷用)';
        Excel::store(
            new YahooLetterExport($yahoo_data), "files/yahoo/{$output_name}.xlsx"
        );
    }


    private function getExcelYahoo($yahoo_id)
    {
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query)  use ($yahoo_id) {
            $query->where('execute_yahoo_id', $yahoo_id);
            }])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        $output_name = '出荷リスト(Yahoo)';
        Excel::store(
            new YahooExport($yahoo_data), "files/yahoo/{$output_name}.xlsx"
        );
    }


    /**
     * レターパック
     *
     * @param $yahoo_id
     * @return void
     */
    private function getLetterPack($yahoo_id)
    {
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id)
                    ->where('file_type', '2');
            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        // ヘッダ取得
        $header = new LetterPack();
        $csvHeader = $header->csvHeader();
        $csvFileName = "Yahooレターパック";
        $csvPath = storage_path("app/private/files/yahoo/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($yahoo_data as $main_index => $row) {
            foreach ($row->YahooItemDetail as $item_index => $row_detail) {
                $row_data = [
                    $row->ShipZipCode,
                    $row->ShipName,
                    "様",
                    $row->ShipPrefecture,
                    $row->ShipCity,
                    $row->ShipAddress1,
                    $row->ShipAddress2,

                    $this->normalizePhone($row->ShipPhoneNumber),
                ];
                fputcsv($file, $this->convertEncoding($row_data));
            }
        }
        fclose($file);
    }


    private function setBaseFile($yahoo_id)
    {
        /*
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        */
        $header_main = new YahooItem();

        $yahoo_data = $header_main->getYahooItem($yahoo_id);

        $csvHeaderMain = $header_main->csvExchangeHeaderMain();
        $csvData = $yahoo_data;
        $csvFileName = "Yahoo全出力リスト";
        $csvPath = storage_path("app/private/files/yahoo/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        fputcsv($file, $this->convertEncoding($csvHeaderMain));

        foreach ($yahoo_data as $main_index => $row) {
            $row_data = [];
            foreach ($row->YahooItemDetail as $item_index => $row_detail) {
                $row_data = [
                    $row->OrderId,
                    $row->BillName,
                    $row->ShipZipCode,
                    $row->ShipName,
                    $row->ShipPrefecture,
                    $row->ShipCity,
                    $row->ShipAddress1,
                    $row->ShipAddress2,
                    $row->ShipSection1,
                    $row->ShipSection2,
                    $this->normalizePhone($row->ShipPhoneNumber),
                    $row->QuantityDetail,
                    $row->BillMailAddress,

                    $row_detail->Title,
                    $row_detail->SubCode,
                    $row_detail->Quantity,
                ];
                fputcsv($file, $this->convertEncoding($row_data));
            }
        }
        fclose($file);
    }

    /**
     * 加工作業シート
     * @param $yahoo_id
     * @return void
     */
    private function setProcessingWork($yahoo_id)
    {
        /*
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        */
        $header_main = new YahooItem();

        $yahoo_data = $header_main->getYahooItem($yahoo_id);

        $csvHeaderMain = $header_main->csvProcessingWorkHeaderMain();
        $csvData = $yahoo_data;
        $csvFileName = "Yahoo加工作業シート";
        $csvPath = storage_path("app/private/files/yahoo/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        fputcsv($file, $this->convertEncoding($csvHeaderMain));

        foreach ($yahoo_data as $main_index => $row) {
            $row_data = [];
            foreach ($row->YahooItemDetail as $item_index => $row_detail) {
                $row_data = [
                    $row->OrderId,
                    $row->BillName,
                    $row->ShipZipCode,
                    $row->ShipName,
                    $row->ShipPrefecture,
                    $row->ShipCity,
                    $row->ShipAddress1,
                    $row->ShipAddress2,
                    $row->ShipSection1,
                    $row->ShipSection2,
                    $this->normalizePhone($row->ShipPhoneNumber),

                    $row_detail->OrderId,
                    $row_detail->LineId,
                    $row_detail->ItemId,
                    $row_detail->Title,
                    $row_detail->SubCode,
                    $row_detail->Quantity,
                    '',
                    $row->BillMailAddress,
                ];
                fputcsv($file, $this->convertEncoding($row_data));
            }
        }
        fclose($file);
    }
    private function getClickPost($yahoo_id)
    {

        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id)
                    ->where('file_type', '1')
                    ->orderBy('Title');

            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
            //->orderBy('type')
            ->get();
        /*
        $yahoo = new YahooItem();
        $yahoo_data  = $yahoo->getYahooItem($yahoo_id);
*/
        $header = new ClickPost();
        $csvHeader = $header->csvHeader();

        $csvFileName = "Yahooクリックポスト";
        $csvPath = storage_path("app/private/files/yahoo/{$csvFileName}.csv");
        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($yahoo_data as $main_index => $row) {
            //dd($yahoo_data);
            foreach ($row->YahooItemDetail as $item_index => $row_detail) {
                $row_data = [
                    $row->ShipZipCode,
                    $row->ShipName,
                    "様",
                    $row->ShipPrefecture,
                    $row->ShipCity,
                    $row->ShipAddress1,
                    $row->ShipAddress2,
                    $row_detail->content,
                ];
                fputcsv($file, $this->convertEncoding($row_data));
            }

        }
        fclose($file);

    }


    private function normalizePhone($tel)
    {   // 数字以外除去
        $tel = preg_replace('/\D/', '', $tel);
        //
        if ($tel[0] !== '0') {
            return '0' . $tel;
        }
        return $tel;
    }

    private function convertEncoding($array)
    {
        return array_map(function ($value) {
            return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
        }, $array);
    }

    private function downloadYahooZip()
    {
        $zipFileName = 'yahooFiles.zip';
        $outputStream = fopen('php://output', 'w');
        $zip = new ZipStream(
            outputStream: $outputStream,
            sendHttpHeaders: true,
            outputName: $zipFileName
        );
        $files = Storage::files('files/yahoo');
        foreach ($files as $file) {
            $stream = Storage::readStream($file);
            $zip->addFileFromStream(fileName: basename($file), stream: $stream);
            fclose($stream);
        }
        $zip->finish();
    }

}
