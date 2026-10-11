<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Portal Routes (Prefix: /admin)
|--------------------------------------------------------------------------
*/

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Protected Admin Routes
Route::middleware('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Product Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::post('/{product}/features', [ProductController::class, 'storeFeature'])->name('features.store');
        Route::delete('/features/{feature}', [ProductController::class, 'deleteFeature'])->name('features.destroy');
    });

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::post('/import-csv', [InventoryController::class, 'importCsv'])->name('import_csv');
        Route::post('/{credential}/reveal', [InventoryController::class, 'reveal'])->name('reveal');
        Route::patch('/{credential}/toggle', [InventoryController::class, 'toggleStatus'])->name('toggle');
        Route::delete('/{credential}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    // Orders Management
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/mark-paid', [OrderController::class, 'markAsPaid'])->name('mark_paid');
        Route::post('/{order}/resend-delivery', [OrderController::class, 'resendDelivery'])->name('resend_delivery');
        Route::post('/{order}/refund', [OrderController::class, 'processRefund'])->name('refund');
        Route::post('/{order}/note', [OrderController::class, 'addNote'])->name('note');
        Route::patch('/{order}/fulfillment', [OrderController::class, 'updateFulfillment'])->name('fulfillment');
        Route::patch('/{order}/canva-status', [OrderController::class, 'updateCanvaStatus'])->name('canva_status');
    });

    // Coupon Management
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::put('/{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy');
    });

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-razorpay', [SettingController::class, 'testRazorpayConnection'])->name('settings.test_razorpay');

    // Testimonials
    Route::prefix('testimonials')->name('testimonials.')->group(function () {
        Route::get('/', [TestimonialController::class, 'index'])->name('index');
        Route::post('/', [TestimonialController::class, 'store'])->name('store');
        Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('update');
        Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy');
    });

    // FAQs
    Route::prefix('faqs')->name('faqs.')->group(function () {
        Route::get('/', [FaqController::class, 'index'])->name('index');
        Route::post('/', [FaqController::class, 'store'])->name('store');
        Route::put('/{faq}', [FaqController::class, 'update'])->name('update');
        Route::delete('/{faq}', [FaqController::class, 'destroy'])->name('destroy');
    });

    // Security & Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});
