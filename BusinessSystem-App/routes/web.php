<?php

use App\Http\Controllers\AmazonDownload;
use App\Http\Controllers\AmazonInputController;
use App\Http\Controllers\RakutenDownloadController;
use App\Http\Controllers\RakutenInputController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');



Route::get('/export', function () {

    // Excel に書き込む配列
    $data = [
        ['ID', 'Product Name', 'Created At'],
        [1, '蒸留水器 専用 クエン酸 クリーナー 500g メガホーム', '2025-09-22 01:20:17'],
        [2, '蒸留水器 専用 ゴムパッキン 台湾メガホーム社製', '2025-09-22 01:20:17'],
        [3, '蒸留水器 マグネット式電源コードタイプ ピュアポット', '2025-09-22 01:20:17'],
    ];

    return Excel::download(new class($data) implements FromArray {
        protected $data;
        public function __construct($data) { $this->data = $data; }
        public function array(): array { return $this->data; }
    }, 'products.xlsx');
});


Route::middleware(['auth', 'verified'])->group(function () {


    Route::get('/index',[AmazonInputController::class, 'index'])->name('amazon.index');
    Route::post('/create',[AmazonInputController::class, 'create'])->name('amazon.create');
    Route::get('/amazonDownload',[AmazonDownload::class,'download'])->name('amazon.download');

    // 楽天
    Route::get('/rakutenIndex', [RakutenInputController::class, 'index'])->name("rakuten.index");
    Route::post('/rakutenCreate', [RakutenInputController::class, 'create'])->name("rakuten.create");
    Route::get('/rakutenDownload',[RakutenDownloadController::class, 'download'])->name("rakuten.download");
    // Yahoo

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
