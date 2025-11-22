<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecuteRakutenManage extends Model
{
    //
    use HasFactory;
    protected $table = 'execute_rakuten_manage';
    protected $guarded = [
      'id',
    ];
}
