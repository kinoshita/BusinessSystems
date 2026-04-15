<?php

namespace App\Exports;

use App\Models\RakutenItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RakutenAllListExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $rakuten_id;

    public function __construct($rakuten_id)
    {
        $this->rakuten_id = $rakuten_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $query = DB::table('rakuten_data')
            ->select(['rakuten_data.*'])
            ->where('execute_rakuten_id', $this->rakuten_id)
            ->orderByRaw('CAST(type AS UNSIGNED) ASC')
            ->get();

        $header = new RakutenItem();
        $row_data = [];
        foreach ($query as $row) {
            //dd($row);
            //$row_data = json_decode(json_encode($row), true);

            //$product_name = preg_replace('/マグネット式電源コードタイプ ピュアポット /', '', $row->product_name);

            $row_data[] = [
                $row->order_id,
                $row->order_last_name . ' ' . $row->order_first_name,
                $row->post_code_1 . '-' . $row->post_code_2,
                $row->destination_last_name . ' ' . $row->destination_first_name,
                $row->prefectures,
                $row->city,
                $row->address,
                $row->telephone_number_1 . '-' . $row->telephone_number_2 . '-' . $row->telephone_number_3,
                $row->quantity,
                $row->product_name,
                $row->unit_price,
                $row->total_product_amount,
            ];
        }
        return collect($row_data);
    }
    public function headings(): array
    {
        return [
            "注文番号",
            "注文者姓名",
            "送付先郵便番号",
            "送付先姓名",
            "送付先住所都道府県",
            "送付先住所郡市区",
            "送付先住所それ以降の住所",
            "送付先電話番号",
            "個数",
            "商品名",
            "単価",
            "商品合計金額"
        ];
    }
}
