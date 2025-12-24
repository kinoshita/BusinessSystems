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

    public function yahooItemDetail()
    {
        return $this->hasMany(YahooItemDetail::class, 'OrderId', 'OrderId');
    }

    public function getYahooItem($yahoo_id)
    {
        $yahoo_data = YahooItem::with([
            'YahooItemDetail' => function ($query) use ($yahoo_id) {
                $query->where('execute_yahoo_id', $yahoo_id);
            }
        ])
            ->where('execute_yahoo_id', $yahoo_id)
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
            'BillMailAddress',
            'Title',
            'SubCode',
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
            'BillMailAddress',

        ];
    }


}
