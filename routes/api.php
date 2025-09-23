<?php

use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupabaseController;
use App\Http\Controllers\FrameworkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\User\AiController;

Route::post('/auth/login', [SupabaseController::class, 'login']);
Route::post('accept-invite', [TeamInvitationController::class, 'acceptInvitation']);

Route::middleware(['auth:supabase', 'role:super_admin'])->group(function () {
    Route::prefix('/admin')->group(function () {
        Route::prefix('/users')->controller(UserController::class)->group(function () {
            Route::get('', 'index');
            Route::get('search', 'search');
            Route::post('', 'store');
            Route::get('{user}', 'show');
            Route::post('{user}', 'update');
            Route::delete('{user}', 'destroy');
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

        Route::prefix('/controls')->controller(ControlController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::get('{control}', 'show');
            Route::post('{control}', 'update');
        });

        Route::prefix('/organizations')->controller(OrganizationController::class)->group(function () {
            Route::get('', 'index');
            Route::get('{organization}', 'show');
            Route::post('{organization}', 'update');
        });

        Route::prefix('/tags')->controller(TagController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
        });
    });
});

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

    Route::prefix('ai-models')->controller(AiController::class)->group(function() {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModel}', 'show');
    });

});
