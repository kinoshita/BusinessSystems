<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class ExecuteYahooManage extends Model
{
    //
    use HasFactory;
    protected $table = 'execute_yahoo_manage';
    protected $guarded = [
        'id',
    ];

    protected $base_directory = 'app/private/files/yahoo';

    public function getYahooBaseDirectory()
    {
        $directory = storage_path($this->base_directory);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0777, true);
        }
        return $this->base_directory;
    }

    public function getYahooCsvDirectory($fileName):String
    {
        $dirPath = storage_path($this->base_directory);
        if (!File::exists($dirPath)) {
            File::makeDirectory($dirPath, 0777, true);
        }
        return "{$dirPath}/{$fileName}.csv";
    }
}
