<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return redirect('/en/home'); // временно
//})->name('home');

Route::any('{any}', [PageController::class, 'handle'])->where('any', '.*');
