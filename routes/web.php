<?php

use App\Http\Controllers\ModalContentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ResendActivationCodeController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return redirect('/en/home'); // временно
//})->name('home');

Route::post('/modal/{action}', ModalContentController::class)
    ->where('action', '.*')
    ->name('modal-content');

Route::post('/auth/login', LoginController::class)->name('auth.login');
Route::post('/modules/auth/login/handler.php', LoginController::class)->name('legacy.auth.login');
Route::post('/auth/activation', ActivationController::class)->name('auth.activation');
Route::post('/modules/auth/activation/handler.php', ActivationController::class)->name('legacy.auth.activation');
Route::post('/auth/activation/resend', ResendActivationCodeController::class)->name('auth.activation.resend');
Route::post('/modules/auth/ajax/resend_activation_code.php', ResendActivationCodeController::class)->name('legacy.auth.activation.resend');

Route::any('{any}', [PageController::class, 'handle'])->where('any', '.*');
