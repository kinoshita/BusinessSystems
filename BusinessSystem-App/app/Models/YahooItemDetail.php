<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YahooItemDetail extends Model
{
    //
    use HasFactory;
    protected $table = 'yahoo_shipping_data';
    protected $guarded = [
        'id'
    ];

    public function yahooItem()
    {
        return $this->belongsTo(YahooItem::class);
    }

    public function csvExchangeHeader():array
    {
        return [
            'OrderId',
            'LineId',
            'ItemId',
            'Title',
            'SubCode',
            'Quantity',
        ];
    }

}
