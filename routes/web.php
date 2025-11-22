<?php

use App\Http\Controllers\ModalContentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ResendActivationCodeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CartController;

//Route::get('/', function () {
//    return redirect('/en/home'); // временно
//})->name('home');

Route::post('/modal/{action}', ModalContentController::class)
    ->where('action', '.*')
    ->name('modal-content');

Route::post('/auth/login', LoginController::class)->name('auth.login');
Route::post('/auth/activation', ActivationController::class)->name('auth.activation');
Route::post('/auth/activation/resend', ResendActivationCodeController::class)->name('auth.activation.resend');

Route::prefix('/cart')->name('cart.')->group(function () {
    Route::post('/add-product', [CartController::class, 'addProduct'])->name('add-product');
    Route::post('/set-product-qty', [CartController::class, 'setProductQuantity'])->name('set-product-qty');
    Route::post('/remove-item', [CartController::class, 'removeItem'])->name('remove-item');
});

Route::post('/credits/top_up', [\App\Http\Controllers\Api\CreditsController::class, 'topUp'])->name('credits.top-up');//

//Route::get('/run-migrate', function () {
//    Artisan::call('migrate', ['--force' => true]);
//    return 'done';
//});

Route::any('{any}', [PageController::class, 'handle'])->where('any', '.*');


