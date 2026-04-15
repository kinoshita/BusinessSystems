<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YahooItem extends Model
{
    //
    use HasFactory;
    protected $table = 'yahoo_data';
    protected $guarded = [
        'id'
    ];

    public function YahooItemDetail()
    {
        return $this->hasMany(YahooItemDetail::class, 'OrderId', 'OrderId')
            ->orderByRaw('CAST(type AS UNSIGNED) ASC');
    }

    public function getYahooItem($yahoo_id)
    {
        $yahoo_data = YahooItem::where('execute_yahoo_id', $yahoo_id)
            ->whereHas('YahooItemDetail', function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            })
            ->with(['YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id)
                    ->orderByRaw('CAST(type AS UNSIGNED) ASC');
            }])
            ->withMin(['YahooItemDetail as min_type' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            }], 'type')
            ->orderByRaw('CAST(min_type AS UNSIGNED) ASC')
            ->get();
        return $yahoo_data;
    }

    public function getYahooItemByOrderId($yahoo_id)
    {
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
            ->orderBy('OrderId', 'asc')
            //->orderBy('type')
            ->get();
        return $yahoo_data;
    }


    public function csvExchangeHeader():array
    {
        return [
            'OrderId',
            'BillName',
            'ShipZipCode',
            'ShipName',
            'ShipPrefecture',
            'ShipCity',
            'ShipAddress1',
            'ShipAddress2',
            'ShipSection1',
            'ShipSection2',
            'ShipPhoneNumber',
            'QuantityDetail',
            'OrderTime',
            'BillMailAddress',
        ];
    }

    public function csvExchangeHeaderMain():array
    {
        return [
            'OrderId',
            'BillName',
            'ShipZipCode',
            'ShipName',
            'ShipPrefecture',
            'ShipCity',
            'ShipAddress1',
            'ShipAddress2',
            'ShipSection1',
            'ShipSection2',
            'ShipPhoneNumber',
            'QuantityDetail',
            'OrderTime',
            'BillMailAddress',
            'Title',
            'SubCode',
            'Quantity',
        ];
    }

    public function csvExchangeHeaderMainForAllOutput():array
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
           // 'OrderTime',
            'Title',
            'Quantity',
        ];
    }




    public function csvProcessingWorkHeaderMain():array
    {
        return [
            'OrderId',
            'BillName',
            'ShipZipCode',
            'ShipName',
            'ShipPrefecture',
            'ShipCity',
            'ShipAddress1',
            'ShipAddress2',
            'ShipSection1',
            'ShipSection2',
            'ShipPhoneNumber',
            'OrderId',
            'LineId',
            'ItemId',
            'Title',
            'SubCode',
            'Quantity',
            'ID確認',
            'OrderTime',
            'BillMailAddress',

        ];
    }




}
