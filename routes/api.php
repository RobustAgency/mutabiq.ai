<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupabaseController;
use App\Http\Controllers\FrameworkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\PaymentMethodController;

Route::post('/auth/login', [SupabaseController::class, 'login']);

Route::middleware(['auth:supabase', 'role:admin'])->group(function () {
    Route::prefix('/admin')->group(function () {
        Route::prefix('/users')->controller(UserController::class)->group(function () {
            Route::get('', 'index');
            Route::get('search', 'search');
            Route::post('', 'store');
            Route::get('{user}', 'show');
        });

        Route::prefix('/frameworks')->controller(FrameworkController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::get('{framework}', 'show');
            Route::post('{framework}', 'update');
        });

        Route::prefix('/requirements')->controller(RequirementController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::get('{requirement}', 'show');
            Route::post('{requirement}', 'update');
        });
    });
});

Route::middleware(['auth:supabase', 'role:user'])->group(function () {
    Route::prefix('/plans')->controller(BillingController::class)->group(function () {
        Route::get('', 'index');
        Route::get('subscribe/{plan}', 'subscribe');
        Route::get('cancel', 'cancel');
        Route::get('invoices', 'invoices');
        Route::get('upcoming-invoice', 'upcomingInvoice');
    });

    Route::prefix('payment-method')->controller(PaymentMethodController::class)->group(function () {
        Route::get('add', 'addPaymentMethod');
    });

    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('', 'show');
    });
});
