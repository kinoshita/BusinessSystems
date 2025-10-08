<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterPack extends Model
{
    //
    public function csvHeader():array
    {
        return [
            "郵便番号",
            "名前",
            "敬称",
            "住所１",
            "住所２",
            "住所３",
            "住所４",
            "連絡先",
        ];
    }
}
