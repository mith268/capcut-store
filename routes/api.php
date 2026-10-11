<?php

use App\Http\Controllers\Api\RazorpayApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Razorpay Standard Checkout API Routes
|--------------------------------------------------------------------------
*/

Route::post('/create-order', [RazorpayApiController::class, 'createOrder'])->name('api.razorpay.create_order');
Route::post('/verify-payment', [RazorpayApiController::class, 'verifyPayment'])->name('api.razorpay.verify_payment');
