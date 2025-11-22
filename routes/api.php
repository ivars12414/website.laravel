<?php

use Illuminate\Support\Facades\Route;
use Spatie\StripeWebhooks\StripeWebhooksController;
use App\Http\Controllers\Api\CartController;

Route::prefix('/api/cart')->name('api.cart.')->group(function () {
    Route::post('/add-product', [CartController::class, 'addProduct'])->name('add-product');
    Route::post('/set-product-qty', [CartController::class, 'setProductQuantity'])->name('set-product-qty');
    Route::post('/remove-item', [CartController::class, 'removeItem'])->name('remove-item');
});

Route::post('/api/top_up', [\App\Http\Controllers\Api\CreditsController::class, 'topUp'])->name('credits.top-up');//

Route::post('/api/webhooks/stripe', StripeWebhooksController::class);
