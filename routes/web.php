<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::prefix('')
    ->controller(\App\Http\Controllers\IndexController::class)->group(function () {
        Route::post('upload-file', 'uploadFile')->name('uploadFile');
        Route::get('get-files', 'getFiles')->name('getFiles');
    });
