<?php

use Illuminate\Support\Facades\Route;
use App\Models\Organization;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\User\FrameworkController;
use App\Http\Controllers\User\MemberController;
use App\Http\Controllers\User\OrganizationController;
use App\Http\Controllers\User\AiController;
use App\Http\Controllers\User\ProjectController;
use App\Http\Controllers\User\AiModelVersionController;
use App\Http\Controllers\User\UseCaseController;
use App\Http\Controllers\User\AiModelCardController;
use App\Http\Controllers\User\StakeholderController;

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

    Route::post('invite-members', [TeamInvitationController::class, 'inviteMembers']);

    Route::prefix('frameworks')->controller(FrameworkController::class)->group(function () {
        Route::get('', 'index');
        Route::get('{framework}', 'show');
    });

    Route::prefix('organizations')->controller(OrganizationController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store')->can('create', Organization::class);
    });

    Route::prefix('members')->controller(MemberController::class)->group(function () {
        Route::get('', 'index');
        Route::put('{user}', 'update');
        Route::delete('{user}', 'destroy');
    });

    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{project}', 'show');
        Route::post('{project}/add-member', 'addMember')->can('addMember', 'project');
        Route::post('{project}/add-frameworks', 'addFrameworks');
    });

    Route::prefix('ai-models')->controller(AiController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModel}', 'show');
    });

    Route::prefix('ai-model-versions')->controller(AiModelVersionController::class)->group(function () {
        Route::post('', 'store');
        Route::get('{aiModelVersion}', 'show');
        Route::post('{aiModelVersion}', 'update');
    });

    Route::prefix('ai-model-cards')->controller(AiModelCardController::class)->group(function () {
        Route::post('', 'store');
        Route::post('{aiModelCard}', 'update');
    });

    Route::prefix('use-cases')->controller(UseCaseController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{useCase}', 'show');
    });

    Route::prefix('stakeholders')->controller(StakeholderController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{stakeholder}', 'show');
        Route::post('{stakeholder}', 'update');
        Route::delete('{stakeholder}', 'destroy');
    });
});
