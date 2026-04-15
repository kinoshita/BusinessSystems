<?php

namespace App\Exports;

use App\Models\YahooItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class YahooAllListExport implements FromCollection, WithHeadings, ShouldAutoSize
{

    protected  $yahoo_id;

    public function __construct($yahoo_id)
    {
        $this->yahoo_id = $yahoo_id;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $header_main = new YahooItem();
        $yahoo_data = $header_main->getYahooItem($this->yahoo_id);

        $all_details = collect($yahoo_data)
            ->flatMap(function ($item) {
                return $item->YahooItemDetail->map(function ($detail) use ($item) {
                    $detail->parent = $item;
                    return $detail;
                });
            });

        $sorted_details = $all_details
            ->sortBy(fn($d) => (int)$d->type)
            ->sortBy(fn($d) => (int)$d->file_type)
            ->values();

        // 👇 ここがCSVとの違い（配列にする）
        return $sorted_details->map(function ($item_detail) {
            $value = $item_detail->parent;

            return [
                $value->BillName,
                $value->ShipZipCode,
                $value->ShipName,
                $value->ShipPrefecture,
                $value->ShipCity,
                $value->ShipAddress1,
                $value->ShipAddress2,
                $this->normalizePhone($value->ShipPhoneNumber),
                $item_detail->Title,
                $item_detail->Quantity,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'BillName',
            'ShipZipCode',
            'ShipName',
            'ShipPrefecture',
            'ShipCity',
            'ShipAddress1',
            'ShipAddress2',
            'ShipPhoneNumber',
            'Title',
            'Quantity',
        ];
    }
    private function normalizePhone($tel)
    {
        $tel = preg_replace('/\D/', '', $tel);
        //
        if ($tel[0] !== '0') {
            return '0' . $tel;
        }
        return $tel;
    }
}
