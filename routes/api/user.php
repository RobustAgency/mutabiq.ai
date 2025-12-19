<?php

use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AiController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\MemberController;
use App\Http\Controllers\User\VendorController;
use App\Http\Controllers\User\AiAssetController;
use App\Http\Controllers\User\DatasetController;
use App\Http\Controllers\User\ProjectController;
use App\Http\Controllers\User\UseCaseController;
use App\Http\Controllers\IncidentAlertController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\IncidentActionController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\User\AgreementController;
use App\Http\Controllers\User\FrameworkController;
use App\Http\Controllers\AiModelArtifactController;
use App\Http\Controllers\User\AiIncidentController;
use App\Http\Controllers\User\DataSourceController;
use App\Http\Controllers\User\AiCommitteeController;
use App\Http\Controllers\User\AiModelCardController;
use App\Http\Controllers\User\DataElementController;
use App\Http\Controllers\User\StakeholderController;
use App\Http\Controllers\User\UserConsentController;
use App\Http\Controllers\User\ConsentScopeController;
use App\Http\Controllers\User\KriIndicatorController;
use App\Http\Controllers\User\OrganizationController;
use App\Http\Controllers\User\ConsentRecordController;
use App\Http\Controllers\User\AiModelDatasetController;
use App\Http\Controllers\User\AiModelUseCaseController;
use App\Http\Controllers\User\AiModelVersionController;
use App\Http\Controllers\User\AiRiskRegisterController;
use App\Http\Controllers\User\DatasetElementController;
use App\Http\Controllers\IncidentNotificationController;
use App\Http\Controllers\User\AiRiskTreatmentController;
use App\Http\Controllers\User\ConsentCoverageController;
use App\Http\Controllers\User\DatasetSnapshotController;
use App\Http\Controllers\User\PrivacyIncidentController;
use App\Http\Controllers\User\RiskMethodologyController;
use App\Http\Controllers\User\ArtifactAccessLogController;
use App\Http\Controllers\User\ComplianceEvidenceController;
use App\Http\Controllers\IncidentRootCauseAnalysisController;
use App\Http\Controllers\User\RegulatorySubmissionController;
use App\Http\Controllers\CorrectivePreventiveActionController;
use App\Http\Controllers\User\PdpProcessingRegisterController;
use App\Http\Controllers\User\DatasetSubjectPopulationController;
use App\Http\Controllers\User\DataSubjectRequestAccessController;
use App\Http\Controllers\User\RecordOfProcessingActivityController;
use App\Http\Controllers\User\DataProtectionImpactAssessmentController;

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

    Route::prefix('organization-users')->controller(UserController::class)->group(function () {
        Route::get('', 'index');
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
        Route::post('{project}', 'update');
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
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModelCard}', 'show');
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
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModelDataset}', 'show');
        Route::post('{aiModelDataset}', 'update');
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

    Route::prefix('ai-assets')->controller(AiAssetController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiAsset}', 'show');
        Route::post('{aiAsset}', 'update');
        Route::delete('{aiAsset}', 'destroy');
    });

    Route::prefix('ai-incidents')->controller(AiIncidentController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiIncident}', 'show');
        Route::post('{aiIncident}', 'update');
        Route::delete('{aiIncident}', 'destroy');
    });

    Route::prefix('incident-alerts')->controller(IncidentAlertController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{incidentAlert}', 'show');
        Route::post('{incidentAlert}', 'update');
        Route::delete('{incidentAlert}', 'destroy');
    });

    Route::prefix('incident-actions')->controller(IncidentActionController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{incidentAction}', 'show');
        Route::post('{incidentAction}', 'update');
        Route::delete('{incidentAction}', 'destroy');
    });

    Route::prefix('incident-root-cause-analyses')->controller(IncidentRootCauseAnalysisController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{incidentRootCauseAnalysis}', 'show');
        Route::post('{incidentRootCauseAnalysis}', 'update');
        Route::delete('{incidentRootCauseAnalysis}', 'destroy');
    });

    Route::prefix('incident-notifications')->controller(IncidentNotificationController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{incidentNotification}', 'show');
        Route::post('{incidentNotification}', 'update');
        Route::delete('{incidentNotification}', 'destroy');
    });

    Route::prefix('corrective-preventive-actions')->controller(CorrectivePreventiveActionController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{correctivePreventiveAction}', 'show');
        Route::post('{correctivePreventiveAction}', 'update');
        Route::delete('{correctivePreventiveAction}', 'destroy');
    });

    Route::prefix('ai-model-artifacts')->controller(AiModelArtifactController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiModelArtifact}', 'show');
        Route::delete('{aiModelArtifact}', 'destroy');
    });

    Route::prefix('artifact-access-logs')->controller(ArtifactAccessLogController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{artifactAccessLog}', 'show');
        Route::delete('{artifactAccessLog}', 'destroy');
    });

    Route::prefix('ai-risk-register')->controller(AiRiskRegisterController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiRiskRegister}', 'show');
        Route::post('{aiRiskRegister}', 'update');
        Route::delete('{aiRiskRegister}', 'destroy');
    });

    Route::prefix('risk-methodologies')->controller(RiskMethodologyController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{riskMethodology}', 'show');
        Route::post('{riskMethodology}', 'update');
        Route::delete('{riskMethodology}', 'destroy');
    });

    Route::prefix('ai-risk-treatments')->controller(AiRiskTreatmentController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiRiskTreatment}', 'show');
        Route::post('{aiRiskTreatment}', 'update');
        Route::delete('{aiRiskTreatment}', 'destroy');
    });

    Route::prefix('kri-indicators')->controller(KriIndicatorController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{kriIndicator}', 'show');
        Route::post('{kriIndicator}', 'update');
        Route::delete('{kriIndicator}', 'destroy');
    });

    Route::prefix('record-of-processing-activities')->controller(RecordOfProcessingActivityController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{recordOfProcessingActivity}', 'show');
        Route::post('{recordOfProcessingActivity}', 'update');
        Route::delete('{recordOfProcessingActivity}', 'destroy');
    });

    Route::prefix('data-subject-request-accesses')->controller(DataSubjectRequestAccessController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{dataSubjectRequestAccess}', 'show');
        Route::post('{dataSubjectRequestAccess}', 'update');
        Route::delete('{dataSubjectRequestAccess}', 'destroy');
    });

    Route::prefix('consent-records')->controller(ConsentRecordController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{consentRecord}', 'show');
        Route::post('{consentRecord}', 'update');
        Route::delete('{consentRecord}', 'destroy');
    });

    Route::prefix('data-protection-impact-assessments')->controller(DataProtectionImpactAssessmentController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{dataProtectionImpactAssessment}', 'show');
        Route::post('{dataProtectionImpactAssessment}', 'update');
        Route::delete('{dataProtectionImpactAssessment}', 'destroy');
    });

    Route::prefix('privacy-incidents')->controller(PrivacyIncidentController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{privacyIncident}', 'show');
        Route::post('{privacyIncident}', 'update');
        Route::delete('{privacyIncident}', 'destroy');
    });

    Route::prefix('/compliance-evidences')->controller(ComplianceEvidenceController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{complianceEvidence}', 'show');
        Route::post('{complianceEvidence}', 'update');
        Route::delete('{complianceEvidence}', 'destroy');
    });

    Route::prefix('/regulatory-submissions')->controller(RegulatorySubmissionController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{regulatorySubmission}', 'show');
        Route::post('{regulatorySubmission}', 'update');
        Route::delete('{regulatorySubmission}', 'destroy');
    });

    Route::prefix('/ai-committees')->controller(AiCommitteeController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{aiCommittee}', 'show');
        Route::post('{aiCommittee}', 'update');
        Route::delete('{aiCommittee}', 'destroy');
    });

});
