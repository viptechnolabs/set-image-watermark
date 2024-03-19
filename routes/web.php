<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::prefix('upload-file')
    ->controller(\App\Http\Controllers\IndexController::class)->group(function () {
        Route::post('', 'uploadFile')->name('uploadFile');
    });
