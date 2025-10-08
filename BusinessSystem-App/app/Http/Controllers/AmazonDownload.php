<?php

namespace App\Http\Controllers;

use App\Exports\AmazonClickPostExport;
use App\Exports\AmazonExport;
use App\Models\AmazonItem;
use App\Models\AmazonYamatoItem;
use App\Models\ClickPost;
use App\Models\LetterPack;
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
        $this->getClickPost($id);
        $this->getExcel($id);
        $this->getLetterPack($id);
        // yamato
        $this->getYamato($id);

     //   $this->getClikPostExcel($id);


        $this->downloadZip();
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
                "product-name as product_name",
                "type as type"
            ])
            ->where('execute_id', $id)
            ->orderBy('type',)
            ->get();
        $header = new AmazonItem();
        $csvHeader = $header->csvExchangeHeader3();
        $csvData = $query;

        $csvFileName = 'export.csv';
        $csvPath = storage_path("app/private/files/{$csvFileName}");

        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($csvData as $row) {
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
            ];
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    private function getClickPost($id)
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
            //->whereNotIn('type', ['9', '3'])
            ->where('file_type', '1')
            ->orderBy('product-name',)
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


        foreach ($csvData as $row) {
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
            ];

            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
    }

    private function getLetterPack($id)
    {
        $query = DB::table('amazon_data')
            ->select([
                'buyer-name as buyer_name',
                'buyer-phone-number as buyer_phone_number',
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
            //->whereNotIn('type', ['9', '3'])
            ->where('file_type', '2')
            //->orderBy('product-name',)
            ->get();

        $header = new LetterPack();
        $csvHeader = $header->csvHeader();
        $csvData = $query;

        $csvFileName = 'LetterPack.csv';
        $csvPath = storage_path("app/private/files/{$csvFileName}");

        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];


        foreach ($csvData as $row) {
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
                $row->buyer_phone_number,
            ];

            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);

    }

    // Excel
    private function getExcel($id)
    {

        $data = AmazonItem::where('execute_id', $id)->first();
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
            ->where('execute_id', $id)
            //->orderBy('type', 'asc')
            ->orderByRaw('CAST(type AS UNSIGNED) ASC')
            ->get();
        Log::info("getExcel");
        Log::info($query);


        // return Excel::download(new AmazonExport($query), 'products.xlsx');
        $output_name = '出荷リスト';
        Excel::store(
            new AmazonExport($query), "files/{$output_name}.xlsx"
        );
    }

    private function getClikPostExcel($id)
    {

        $data = AmazonItem::where('execute_id', $id)->first();
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
            ->where('execute_id', $id)
            //->orderBy('type', 'asc')
            ->orderByRaw('CAST(type AS UNSIGNED) ASC')
            ->get();
        Log::info("getExcel");
        Log::info($query);


        // return Excel::download(new AmazonExport($query), 'products.xlsx');
        $output_name = 'クリックリスト';
        Excel::store(
            new AmazonClickPostExport($query), "files/{$output_name}.xlsx"
        );
    }

    private function getYamato($id)
    {
        $query = DB::table('amazon_data_yamato_transport_ltd')
            ->select([
                "order-id as order_id",
                "order-item-id as order_item_id",
                "purchase-date as purchase_date",
                "payments-date as payments_date",
                "reporting-date as reporting_date",
                "promise-date as promise_date",
                "days-past-promise as days_past_promise",
                "buyer-email as buyer_email",
                "buyer-name as buyer_name",
                "buyer-phone-number as buyer_phone_number",
                "sku",
                "product-name as product_name",
                "quantity-purchased as quantity_purchased",
                "quantity-shipped as quantity_shipped",
                "quantity-to-ship as quantity_to_ship",
                "ship-service-level as ship_service_level",
                "recipient-name as recipient_name",
                "ship-address-1 as ship_address_1",
                "ship-address-2 as ship_address_2",
                "ship-address-3 as ship_address_3",
                "ship-city as ship_city",
                "ship-state as ship_state",
                "ship-postal-code as ship_postal_code",
                "ship-country as ship_country",
                "payment-method as payment_method",
                "cod-collectible-amount as cod_collectible_amount",
                "already-paid as already_paid",
                "payment-method-fee as payment_method_fee",
                "scheduled-delivery-start-date as scheduled_delivery_start_date",
                "scheduled-delivery-end-date as scheduled_delivery_end_date",
                "points-granted as points_granted",
                "is-prime as is_prime",
                "verge-of-cancellation as verge_of_cancellation",
                "verge-of-lateShipment as verge_of_lateShipment",
            ])
            ->where('execute_id', $id)
        //->orderBy('type',)
        ->get();
        $header = new AmazonYamatoItem();
        $csvHeader = $header->csvHeader();
        $csvData = $query;
//dd($query);
        $csvFileName = 'yamato.csv';
        $csvPath = storage_path("app/private/files/{$csvFileName}");

        $file = fopen($csvPath, 'w');
        // ヘッダー行
        fputcsv($file, $this->convertEncoding($csvHeader));
        $row_data = [];
        foreach ($csvData as $row) {
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);
            $row_data = [
                $row->order_id,
                $row->order_item_id,
                $row->purchase_date,
                $row->payments_date,
                $row->reporting_date,
                $row->promise_date,
                $row->days_past_promise,
                $row->buyer_email,
                $row->buyer_name,
                $row->buyer_phone_number,
                $row->sku,
                $row->product_name,
                $row->quantity_purchased,
                $row->quantity_shipped,
                $row->quantity_to_ship,
                $row->ship_service_level,
                $row->recipient_name,
                $row->ship_address_1,
                $row->ship_address_2,
                $row->ship_address_3,
                $row->ship_city,
                $row->ship_state,
                $row->ship_postal_code,
                $row->ship_country,
                $row->payment_method,
                $row->cod_collectible_amount,
                $row->already_paid,
                $row->payment_method_fee,
                $row->scheduled_delivery_start_date,
                $row->scheduled_delivery_end_date,
                $row->points_granted,
                $row->is_prime,
                $row->verge_of_cancellation,
                $row->verge_of_lateShipment,
            ];
            fputcsv($file, $this->convertEncoding($row_data));
        }
        fclose($file);
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
