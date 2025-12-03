<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Admin\FrameworkController;
use App\Http\Controllers\Admin\ComplianceEvidenceController;
use App\Http\Controllers\Admin\RequirementControlController;

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

        Route::prefix('/requirement-controls')->controller(RequirementControlController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::get('{requirementControl}', 'show');
            Route::post('{requirementControl}', 'update');
            Route::delete('{requirementControl}', 'destroy');
        });

        Route::prefix('/compliance-evidences')->controller(ComplianceEvidenceController::class)->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::get('{complianceEvidence}', 'show');
            Route::post('{complianceEvidence}', 'update');
            Route::delete('{complianceEvidence}', 'destroy');
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
