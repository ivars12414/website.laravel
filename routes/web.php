<?php

use App\Http\Controllers\ModalContentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return redirect('/en/home'); // временно
//})->name('home');

Route::post('/modal/{action}', ModalContentController::class)
    ->where('action', '.*')
    ->name('modal-content');

Route::post('/auth/login', LoginController::class)->name('auth.login');
Route::post('/modules/auth/login/handler.php', LoginController::class)->name('legacy.auth.login');

Route::any('{any}', [PageController::class, 'handle'])->where('any', '.*');
