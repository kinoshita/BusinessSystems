<?php

namespace App\Http\Controllers;

use App\Exports\AmazonExport;
use App\Models\AmazonItem;
use App\Models\ClickPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use STS\ZipStream\Facades\Zip;
use Symfony\Component\HttpFoundation\StreamedResponse;
//use ZipArchive;
use ZipStream\ZipStream;


class AmazonDownload extends Controller
{
    //
    public function download(Request $request)
    {

        Log::info("download");
        Log::info($request->input('download'));
        $id = $request->input('download');

        $this->setBaseFile($id);


        //dd($callback);

      /*  return response()->streamDownload($callback, 'test.csv',
            [
                'Content-Type' => 'text/csv; charset=Shift_JIS',
            ]);
      */

       $this->getClickPost();
       $this->getExcel();
        //$this->downloadZip();
    }

    private function convertEncoding($array)
    {
        return array_map(function ($value) {
            return mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
        }, $array);
    }

    private function setBaseFile($id)
    {
        $query = DB::table('amazon_data')
            ->select([
                'buyer-name as buyer_name',
                'ship-postal-code as ship_postal_code',
                "recipient-name as recipient_name",
                "ship-state as ship_state",
                "ship-address-1 as ship_address_1",
                "ship-address-2 as ship_address_2",
                "ship-address-3 as ship_address_3",
                "内容品 as content",
                "quantity-to-ship as quantity_to_ship",
                "product-name as product_name"
            ])
            ->where('execute_id', $id)
            ->orderBy('product-name', )
            ->get();
        $header = new AmazonItem();
        $csvHeader = $header->csvExchangeHeader2();
        $csvData = $query;

        $csvFileName = 'export.csv';
        $csvPath = storage_path("app/private/files/{$csvFileName}");

        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach($csvData as $row){
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);
            $row_data = [
                $row->buyer_name,
                $row->ship_postal_code,
                $row->recipient_name,
                $row->ship_state,
                $row->ship_address_1,
                $row->ship_address_2,
                $row->ship_address_3,
                $row->content,
                $row->quantity_to_ship,
                $row->product_name
            ] ;
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    private function getClickPost()
    {
        $query = DB::table('amazon_data')
            ->select([
                'buyer-name as buyer_name',
                'ship-postal-code as ship_postal_code',
                "recipient-name as recipient_name",
                "ship-state as ship_state",
                "ship-address-1 as ship_address_1",
                "ship-address-2 as ship_address_2",
                "ship-address-3 as ship_address_3",
                "内容品 as content",
                "quantity-to-ship as quantity_to_ship",
                "product-name as product_name"
            ])
            ->orderBy('product-name', )
            ->get();
        $header = new ClickPost();
        $csvHeader = $header->csvHeader();
        $csvData = $query;

        $csvFileName = 'clickPost.csv';
        $csvPath = storage_path("app/private/files/{$csvFileName}");

        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];



        foreach($csvData as $row){
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);
            $row_data = [
                $row->ship_postal_code,
                $row->recipient_name,
                '様',
                $row->ship_state,
                $row->ship_address_1,
                $row->ship_address_2,
                $row->ship_address_3,
                $row->content,
            ] ;

            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    // Excel
    private function getExcel()
    {
        $execute_id = 1;
        $data = AmazonItem::where('execute_id', $execute_id)->first();
        $query = DB::table('amazon_data')
            ->select([
                'buyer-name as buyer_name',
                'ship-postal-code as ship_postal_code',
                "recipient-name as recipient_name",
                "ship-state as ship_state",
                "ship-address-1 as ship_address_1",
                "ship-address-2 as ship_address_2",
                "ship-address-3 as ship_address_3",
                "内容品 as content",
                "quantity-to-ship as quantity_to_ship",
                "product-name as product_name",
                "type"
            ])
            ->orderBy('product-name', )
            ->get();

       // return Excel::download(new AmazonExport($query), 'products.xlsx');
          Excel::store(
              new AmazonExport($query), 'files/products.xlsx'
          );
    }

    public function downloadZip()
    {
        // create a new zipstream object
        $zipFileName = 'files.zip';

        $outputStream = fopen('php://output', 'w');

        $zip = new ZipStream(
            outputStream: $outputStream,
            sendHttpHeaders: true,
            outputName: $zipFileName
        );

        $files = Storage::files('files');
        //$files = Storage::disk('local')->files('files');
        //dd($files);
        foreach ($files as $file) {
            $stream = Storage::readStream($file);
            $zip->addFileFromStream(fileName: basename($file), stream: $stream);
            fclose($stream);
        }

        $zip->finish();
    }




}
