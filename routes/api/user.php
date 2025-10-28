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
use App\Http\Controllers\User\AiModelUseCaseController;
use App\Http\Controllers\User\AiModelCardController;
use App\Http\Controllers\User\StakeholderController;
use App\Http\Controllers\User\DataSourceController;
use App\Http\Controllers\User\DatasetController;
use App\Http\Controllers\User\DataElementController;
use App\Http\Controllers\User\DatasetElementController;
use App\Http\Controllers\User\DatasetSnapshotController;
use App\Http\Controllers\User\AiModelDatasetController;
use App\Http\Controllers\User\UserConsentController;
use App\Http\Controllers\User\ConsentScopeController;
use App\Http\Controllers\User\ConsentCoverageController;
use App\Http\Controllers\User\DatasetSubjectPopulationController;
use App\Http\Controllers\User\PdpProcessingRegisterController;
use App\Http\Controllers\User\VendorController;
use App\Http\Controllers\User\AgreementController;

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
        Route::post('{project}/add-framework', 'addFramework');
    });

    Route::prefix('ai-models')->controller(AiController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModel}', 'show');
    });

    Route::prefix('ai-model-versions')->controller(AiModelVersionController::class)->group(function () {
        Route::get('', 'index');
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

    Route::prefix('ai-model-use-cases')->controller(AiModelUseCaseController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModelUseCase}', 'show');
        Route::post('{aiModelUseCase}', 'update');
        Route::delete('{aiModelUseCase}', 'destroy');
    });

    Route::prefix('stakeholders')->controller(StakeholderController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{stakeholder}', 'show');
        Route::post('{stakeholder}', 'update');
        Route::delete('{stakeholder}', 'destroy');
    });

    Route::prefix('data-sources')->controller(DataSourceController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{dataSource}', 'show');
        Route::post('{dataSource}', 'update');
        Route::delete('{dataSource}', 'destroy');
    });

    Route::prefix('datasets')->controller(DatasetController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{dataset}', 'show');
        Route::post('{dataset}', 'update');
        Route::delete('{dataset}', 'destroy');
    });

    Route::prefix('data-elements')->controller(DataElementController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{dataElement}', 'show');
        Route::post('{dataElement}', 'update');
        Route::delete('{dataElement}', 'destroy');
    });

    Route::prefix(('associate-data-element-with-dataset'))->controller(DatasetElementController::class)->group(function () {
        Route::post('', 'store');
    });

    Route::prefix('dataset-snapshots')->controller(DatasetSnapshotController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{datasetSnapshot}', 'show');
        Route::post('{datasetSnapshot}', 'update');
        Route::delete('{datasetSnapshot}', 'destroy');
    });

    Route::prefix('ai-model-datasets')->controller(AiModelDatasetController::class)->group(function () {
        Route::post('', 'store');
    });

    Route::prefix('user-consents')->controller(UserConsentController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{userConsent}', 'show');
        Route::put('{userConsent}', 'update');
        Route::delete('{userConsent}', 'destroy');
    });

    Route::prefix('consent-scopes')->controller(ConsentScopeController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{consentScope}', 'show');
        Route::put('{consentScope}', 'update');
        Route::delete('{consentScope}', 'destroy');
    });

    Route::prefix('consent-coverages')->controller(ConsentCoverageController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{consentCoverage}', 'show');
        Route::post('{consentCoverage}', 'update');
        Route::delete('{consentCoverage}', 'destroy');
    });

    Route::prefix('dataset-subject-populations')->controller(DatasetSubjectPopulationController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{datasetSubjectPopulation}', 'show');
        Route::post('{datasetSubjectPopulation}', 'update');
        Route::delete('{datasetSubjectPopulation}', 'destroy');
    });

    Route::prefix('pdp-processing-registers')->controller(PdpProcessingRegisterController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{pdpProcessingRegister}', 'show');
        Route::post('{pdpProcessingRegister}', 'update');
        Route::delete('{pdpProcessingRegister}', 'destroy');
    });

    Route::prefix('vendors')->controller(VendorController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{vendor}', 'show');
        Route::post('{vendor}', 'update');
        Route::delete('{vendor}', 'destroy');
    });

    Route::prefix('agreements')->controller(AgreementController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{agreement}', 'show');
        Route::post('{agreement}', 'update');
        Route::delete('{agreement}', 'destroy');
    });
});
