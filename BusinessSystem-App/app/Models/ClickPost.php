<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickPost extends Model
{
    //
    public function csvHeader():array
    {
        return [
            "お届け先郵便番号",
            "お届け先氏名",
            "お届け先敬称",
            "お届け先住所1行目",
            "お届け先住所2行目",
            "お届け先住所3行目",
            "お届け先住所4行目",
            "内容品",
        ];
    }
}
