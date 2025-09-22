<?php

use App\Http\Controllers\AmazonDownload;
use App\Http\Controllers\AmazonInputController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/amazonDownload',[AmazonDownload::class,'download']);

Route::middleware(['auth', 'verified'])->group(function () {


    Route::get('/index',[AmazonInputController::class, 'index'])->name('amazon.index');
    Route::post('/create',[AmazonInputController::class, 'create'])->name('amazon.create');





    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
