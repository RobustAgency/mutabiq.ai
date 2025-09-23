<?php

use Illuminate\Support\Facades\Route;
use App\Models\Organization;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\User\FrameworkController;

Route::middleware(['auth:supabase'])->group(function () {
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

    Route::get('profile', [ProfileController::class, 'show']);

    Route::post('organizations', [OrganizationController::class, 'store'])->can('create', Organization::class);

    Route::post('invite-members', [TeamInvitationController::class, 'inviteMembers']);

    Route::prefix('frameworks')->controller(FrameworkController::class)->group(function () {
        Route::get('', 'index');
        Route::get('{framework}', 'show');
    });
});