<?php

use App\Http\Controllers\ModalContentController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return redirect('/en/home'); // временно
//})->name('home');

Route::post('/modal/{action}', ModalContentController::class)
    ->where('action', '.*')
    ->name('modal-content');

Route::any('{any}', [PageController::class, 'handle'])->where('any', '.*');
