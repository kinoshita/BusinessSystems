<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RakutenItem extends Model
{
    //
    use HasFactory;

    protected $table = 'rakuten_data';
    protected $guarded = [
        'id',
    ];

    public function csvExchangeHeader(): array
    {
        return [
            "注文番号",
            "注文者姓",
            "注文者名",
            "送付先郵便番号1",
            "送付先郵便番号2",
            "送付先姓",
            "送付先名",
            "送付先住所都道府県",
            "送付先住所郡市区",
            "送付先住所それ以降の住所",
            "送付先電話番号1",
            "送付先電話番号2",
            "送付先電話番号3",
            "個数",
            "商品名",
            "単価",
            "商品合計金額"
        ];
    }


}
