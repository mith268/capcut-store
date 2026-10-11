<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront & Customer Routes
|--------------------------------------------------------------------------
*/

// Homepage (Configurable: Landing offer or Store catalog)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Multi-Product Store Catalog & Product Details
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/category/{slug}', [StoreController::class, 'category'])->name('category.show');
Route::get('/products/{slug}', [StoreController::class, 'product'])->name('products.show');
Route::redirect('/store/{slug}', '/products/{slug}', 301);
Route::redirect('/product/{slug}', '/products/{slug}', 301);
Route::redirect('/products', '/store', 301);

// Checkout Flow
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'show'])->name('show');
    Route::post('/calculate', [CheckoutController::class, 'calculate'])->name('calculate');
    Route::post('/initiate', [CheckoutController::class, 'initiate'])
        ->middleware('throttle:20,1')
        ->name('initiate');
    Route::post('/autosave', [CheckoutController::class, 'autosave'])
        ->middleware('throttle:60,1')
        ->name('autosave');
    Route::post('/canva-bonus', [CheckoutController::class, 'claimCanva'])->name('canva_bonus');
});

// Payment Gateways
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/verify', [PaymentController::class, 'verify'])
        ->middleware('throttle:30,1')
        ->name('verify');
    Route::post('/failure', [PaymentController::class, 'failure'])->name('failure');
});

// Razorpay Webhook (CSRF excluded in bootstrap/app.php)
Route::post('/webhooks/razorpay', [WebhookController::class, 'handleRazorpay'])->name('webhook.razorpay');

// Customer Secure Order Portal
Route::prefix('order')->name('order.')->group(function () {
    Route::get('/lookup', [OrderController::class, 'lookupForm'])->name('lookup');
    Route::post('/lookup', [OrderController::class, 'lookupProcess'])->name('find');
    Route::get('/{uuid}/{token}', [OrderController::class, 'show'])->name('show');
    Route::post('/{uuid}/{token}/reveal', [OrderController::class, 'reveal'])->name('reveal');
    Route::post('/{uuid}/{token}/canva-bonus', [OrderController::class, 'claimCanvaBonus'])->name('canva_bonus');
});

// Marketing & Tracking
Route::post('/track-event', [TrackingController::class, 'track'])->name('tracking.event');

// Legal & Information Pages
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('page.terms');
Route::get('/refund-policy', [PageController::class, 'refund'])->name('page.refund');
Route::get('/warranty-policy', [PageController::class, 'warranty'])->name('page.warranty');
Route::get('/support', [PageController::class, 'support'])->name('page.support');
Route::post('/support/submit', [PageController::class, 'submitSupport'])
    ->middleware('throttle:5,1')
    ->name('page.support.submit');

Route::get('/sitemap.xml', function () {
    return response()->view('pages.sitemap')->header('Content-Type', 'application/xml');
});

