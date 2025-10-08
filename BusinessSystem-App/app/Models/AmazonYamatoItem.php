<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonYamatoItem extends Model
{
    //
    use HasFactory;
    protected $table = 'amazon_data_yamato_transport_ltd';
    protected $guarded = [
        'id',
    ];
}
