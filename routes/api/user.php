<?php

use App\Models\Organization;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AiController;
use App\Http\Controllers\User\RoleController;
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
use App\Http\Controllers\User\PermissionController;
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
use App\Http\Controllers\User\CommitteeActionController;
use App\Http\Controllers\User\ConsentCoverageController;
use App\Http\Controllers\User\DatasetSnapshotController;
use App\Http\Controllers\User\PrivacyIncidentController;
use App\Http\Controllers\User\RiskMethodologyController;
use App\Http\Controllers\User\CommitteeMeetingController;
use App\Http\Controllers\User\ArtifactAccessLogController;
use App\Http\Controllers\User\CommitteeDecisionController;
use App\Http\Controllers\User\ComplianceEvidenceController;
use App\Http\Controllers\User\CommitteeMembershipController;
use App\Http\Controllers\IncidentRootCauseAnalysisController;
use App\Http\Controllers\User\RegulatorySubmissionController;
use App\Http\Controllers\CorrectivePreventiveActionController;
use App\Http\Controllers\User\PdpProcessingRegisterController;
use App\Http\Controllers\User\DatasetSubjectPopulationController;
use App\Http\Controllers\User\DataSubjectRequestAccessController;
use App\Http\Controllers\User\RecordOfProcessingActivityController;
use App\Http\Controllers\User\DataProtectionImpactAssessmentController;

Route::middleware(['auth:supabase'])->group(function () {

    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('', 'index');
        Route::get('{user}', 'show');
        Route::post('{user}/assign-role', 'assignRole');
        Route::delete('{user}/revoke-role/{role}', 'revokeRole');
        Route::post('{user}/assign-permission', 'assignPermission');
        Route::post('{user}/revoke-permission', 'revokePermission');
    });

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

    Route::prefix('permissions')->controller(PermissionController::class)->group(function () {
        Route::get('', 'index');
    });

    Route::prefix('roles')->controller(RoleController::class)->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
        Route::get('{role}', 'show');
        Route::post('{role}', 'update');
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
        Route::get('', 'index')->middleware('permission:core-assets.ai-models.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-models.create');
        Route::get('{aiModel}', 'show')->middleware('permission:core-assets.ai-models.view');
    });

    Route::prefix('ai-model-versions')->controller(AiModelVersionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-model-versions.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-model-versions.create');
        Route::get('{aiModelVersion}', 'show')->middleware('permission:core-assets.ai-model-versions.view');
        Route::post('{aiModelVersion}', 'update')->middleware('permission:core-assets.ai-model-versions.edit');
    });

    Route::prefix('ai-model-cards')->controller(AiModelCardController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-model-cards.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-model-cards.create');
        Route::get('{aiModelCard}', 'show')->middleware('permission:core-assets.ai-model-cards.view');
        Route::post('{aiModelCard}', 'update')->middleware('permission:core-assets.ai-model-cards.edit');
    });

    Route::prefix('use-cases')->controller(UseCaseController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.use-cases.view');
        Route::post('', 'store')->middleware('permission:core-assets.use-cases.create');
        Route::get('{useCase}', 'show')->middleware('permission:core-assets.use-cases.view');
    });

    Route::prefix('ai-model-use-cases')->controller(AiModelUseCaseController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-model-use-cases.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-model-use-cases.create');
        Route::get('{aiModelUseCase}', 'show')->middleware('permission:core-assets.ai-model-use-cases.view');
        Route::post('{aiModelUseCase}', 'update')->middleware('permission:core-assets.ai-model-use-cases.edit');
        Route::delete('{aiModelUseCase}', 'destroy')->middleware('permission:core-assets.ai-model-use-cases.delete');
    });

    Route::prefix('stakeholders')->controller(StakeholderController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.stakeholders.view');
        Route::get('statistics', 'statistics')->middleware('permission:core-assets.stakeholders.view');
        Route::post('', 'store')->middleware('permission:core-assets.stakeholders.create');
        Route::get('{stakeholder}', 'show')->middleware('permission:core-assets.stakeholders.view');
        Route::post('{stakeholder}', 'update')->middleware('permission:core-assets.stakeholders.edit');
        Route::delete('{stakeholder}', 'destroy')->middleware('permission:core-assets.stakeholders.delete');
    });

    Route::prefix('data-sources')->controller(DataSourceController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.data-sources.view');
        Route::post('', 'store')->middleware('permission:core-assets.data-sources.create');
        Route::get('{dataSource}', 'show')->middleware('permission:core-assets.data-sources.view');
        Route::post('{dataSource}', 'update')->middleware('permission:core-assets.data-sources.edit');
        Route::delete('{dataSource}', 'destroy')->middleware('permission:core-assets.data-sources.delete');
    });

    Route::prefix('datasets')->controller(DatasetController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.datasets.view');
        Route::post('', 'store')->middleware('permission:core-assets.datasets.create');
        Route::get('{dataset}', 'show')->middleware('permission:core-assets.datasets.view');
        Route::post('{dataset}', 'update')->middleware('permission:core-assets.datasets.edit');
        Route::delete('{dataset}', 'destroy')->middleware('permission:core-assets.datasets.delete');
    });

    Route::prefix('data-elements')->controller(DataElementController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.data-elements.view');
        Route::post('', 'store')->middleware('permission:core-assets.data-elements.create');
        Route::get('{dataElement}', 'show')->middleware('permission:core-assets.data-elements.view');
        Route::post('{dataElement}', 'update')->middleware('permission:core-assets.data-elements.edit');
        Route::delete('{dataElement}', 'destroy')->middleware('permission:core-assets.data-elements.delete');
    });

    Route::prefix(('associate-data-element-with-dataset'))->controller(DatasetElementController::class)->group(function () {
        Route::post('', 'store')->middleware('permission:core-assets.data-elements.create');
    });

    Route::prefix('dataset-snapshots')->controller(DatasetSnapshotController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.dataset-snapshots.view');
        Route::post('', 'store')->middleware('permission:core-assets.dataset-snapshots.create');
        Route::get('{datasetSnapshot}', 'show')->middleware('permission:core-assets.dataset-snapshots.view');
        Route::post('{datasetSnapshot}', 'update')->middleware('permission:core-assets.dataset-snapshots.edit');
        Route::delete('{datasetSnapshot}', 'destroy')->middleware('permission:core-assets.dataset-snapshots.delete');
    });

    Route::prefix('ai-model-datasets')->controller(AiModelDatasetController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-model-datasets.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-model-datasets.create');
        Route::get('{aiModelDataset}', 'show')->middleware('permission:core-assets.ai-model-datasets.view');
        Route::post('{aiModelDataset}', 'update')->middleware('permission:core-assets.ai-model-datasets.edit');
    });

    Route::prefix('user-consents')->controller(UserConsentController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.user-consents.view');
        Route::post('', 'store')->middleware('permission:core-assets.user-consents.create');
        Route::get('{userConsent}', 'show')->middleware('permission:core-assets.user-consents.view');
        Route::put('{userConsent}', 'update')->middleware('permission:core-assets.user-consents.edit');
        Route::delete('{userConsent}', 'destroy')->middleware('permission:core-assets.user-consents.delete');
    });

    Route::prefix('consent-scopes')->controller(ConsentScopeController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.consent-scopes.view');
        Route::post('', 'store')->middleware('permission:core-assets.consent-scopes.create');
        Route::get('{consentScope}', 'show')->middleware('permission:core-assets.consent-scopes.view');
        Route::put('{consentScope}', 'update')->middleware('permission:core-assets.consent-scopes.edit');
        Route::delete('{consentScope}', 'destroy')->middleware('permission:core-assets.consent-scopes.delete');
    });

    Route::prefix('consent-coverages')->controller(ConsentCoverageController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.consent-coverages.view');
        Route::post('', 'store')->middleware('permission:core-assets.consent-coverages.create');
        Route::get('{consentCoverage}', 'show')->middleware('permission:core-assets.consent-coverages.view');
        Route::post('{consentCoverage}', 'update')->middleware('permission:core-assets.consent-coverages.edit');
        Route::delete('{consentCoverage}', 'destroy')->middleware('permission:core-assets.consent-coverages.delete');
    });

    Route::prefix('dataset-subject-populations')->controller(DatasetSubjectPopulationController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.dataset-subject-populations.view');
        Route::post('', 'store')->middleware('permission:core-assets.dataset-subject-populations.create');
        Route::get('{datasetSubjectPopulation}', 'show')->middleware('permission:core-assets.dataset-subject-populations.view');
        Route::post('{datasetSubjectPopulation}', 'update')->middleware('permission:core-assets.dataset-subject-populations.edit');
        Route::delete('{datasetSubjectPopulation}', 'destroy')->middleware('permission:core-assets.dataset-subject-populations.delete');
    });

    Route::prefix('pdp-processing-registers')->controller(PdpProcessingRegisterController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.pdp-processing-registers.view');
        Route::post('', 'store')->middleware('permission:core-assets.pdp-processing-registers.create');
        Route::get('{pdpProcessingRegister}', 'show')->middleware('permission:core-assets.pdp-processing-registers.view');
        Route::post('{pdpProcessingRegister}', 'update')->middleware('permission:core-assets.pdp-processing-registers.edit');
        Route::delete('{pdpProcessingRegister}', 'destroy')->middleware('permission:core-assets.pdp-processing-registers.delete');
    });

    Route::prefix('vendors')->controller(VendorController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.vendors.view');
        Route::get('statistics', 'statistics')->middleware('permission:core-assets.vendors.view');
        Route::post('', 'store')->middleware('permission:core-assets.vendors.create');
        Route::get('{vendor}', 'show')->middleware('permission:core-assets.vendors.view');
        Route::post('{vendor}', 'update')->middleware('permission:core-assets.vendors.edit');
        Route::delete('{vendor}', 'destroy')->middleware('permission:core-assets.vendors.delete');
    });

    Route::prefix('agreements')->controller(AgreementController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.agreements.view');
        Route::get('statistics', 'statistics')->middleware('permission:core-assets.agreements.view');
        Route::post('', 'store')->middleware('permission:core-assets.agreements.create');
        Route::get('{agreement}', 'show')->middleware('permission:core-assets.agreements.view');
        Route::post('{agreement}', 'update')->middleware('permission:core-assets.agreements.edit');
        Route::delete('{agreement}', 'destroy')->middleware('permission:core-assets.agreements.delete');
    });

    Route::prefix('ai-assets')->controller(AiAssetController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-assets.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-assets.create');
        Route::get('{aiAsset}', 'show')->middleware('permission:core-assets.ai-assets.view');
        Route::post('{aiAsset}', 'update')->middleware('permission:core-assets.ai-assets.edit');
        Route::delete('{aiAsset}', 'destroy')->middleware('permission:core-assets.ai-assets.delete');
    });

    Route::prefix('ai-incidents')->controller(AiIncidentController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.ai-incidents.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.ai-incidents.create');
        Route::get('{aiIncident}', 'show')->middleware('permission:governance-and-oversight.ai-incidents.view');
        Route::post('{aiIncident}', 'update')->middleware('permission:governance-and-oversight.ai-incidents.edit');
        Route::delete('{aiIncident}', 'destroy')->middleware('permission:governance-and-oversight.ai-incidents.delete');
    });

    Route::prefix('incident-alerts')->controller(IncidentAlertController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.incident-alerts.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.incident-alerts.create');
        Route::get('{incidentAlert}', 'show')->middleware('permission:governance-and-oversight.incident-alerts.view');
        Route::post('{incidentAlert}', 'update')->middleware('permission:governance-and-oversight.incident-alerts.edit');
        Route::delete('{incidentAlert}', 'destroy')->middleware('permission:governance-and-oversight.incident-alerts.delete');
    });

    Route::prefix('incident-actions')->controller(IncidentActionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.incident-actions.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.incident-actions.create');
        Route::get('{incidentAction}', 'show')->middleware('permission:governance-and-oversight.incident-actions.view');
        Route::post('{incidentAction}', 'update')->middleware('permission:governance-and-oversight.incident-actions.edit');
        Route::delete('{incidentAction}', 'destroy')->middleware('permission:governance-and-oversight.incident-actions.delete');
    });

    Route::prefix('incident-root-cause-analyses')->controller(IncidentRootCauseAnalysisController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.incident-root-cause-analyses.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.incident-root-cause-analyses.create');
        Route::get('{incidentRootCauseAnalysis}', 'show')->middleware('permission:governance-and-oversight.incident-root-cause-analyses.view');
        Route::post('{incidentRootCauseAnalysis}', 'update')->middleware('permission:governance-and-oversight.incident-root-cause-analyses.edit');
        Route::delete('{incidentRootCauseAnalysis}', 'destroy')->middleware('permission:governance-and-oversight.incident-root-cause-analyses.delete');
    });

    Route::prefix('incident-notifications')->controller(IncidentNotificationController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.incident-notifications.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.incident-notifications.create');
        Route::get('{incidentNotification}', 'show')->middleware('permission:governance-and-oversight.incident-notifications.view');
        Route::post('{incidentNotification}', 'update')->middleware('permission:governance-and-oversight.incident-notifications.edit');
        Route::delete('{incidentNotification}', 'destroy')->middleware('permission:governance-and-oversight.incident-notifications.delete');
    });

    Route::prefix('corrective-preventive-actions')->controller(CorrectivePreventiveActionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.corrective-preventive-actions.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.corrective-preventive-actions.create');
        Route::get('{correctivePreventiveAction}', 'show')->middleware('permission:governance-and-oversight.corrective-preventive-actions.view');
        Route::post('{correctivePreventiveAction}', 'update')->middleware('permission:governance-and-oversight.corrective-preventive-actions.edit');
        Route::delete('{correctivePreventiveAction}', 'destroy')->middleware('permission:governance-and-oversight.corrective-preventive-actions.delete');
    });

    Route::prefix('ai-model-artifacts')->controller(AiModelArtifactController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.ai-model-artifacts.view');
        Route::post('', 'store')->middleware('permission:core-assets.ai-model-artifacts.create');
        Route::get('{aiModelArtifact}', 'show')->middleware('permission:core-assets.ai-model-artifacts.view');
        Route::delete('{aiModelArtifact}', 'destroy')->middleware('permission:core-assets.ai-model-artifacts.delete');
    });

    Route::prefix('artifact-access-logs')->controller(ArtifactAccessLogController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.artifact-access-logs.view');
        Route::post('', 'store')->middleware('permission:core-assets.artifact-access-logs.create');
        Route::get('{artifactAccessLog}', 'show')->middleware('permission:core-assets.artifact-access-logs.view');
        Route::delete('{artifactAccessLog}', 'destroy')->middleware('permission:core-assets.artifact-access-logs.delete');
    });

    Route::prefix('ai-risk-register')->controller(AiRiskRegisterController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.ai-risk-register.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.ai-risk-register.create');
        Route::get('{aiRiskRegister}', 'show')->middleware('permission:risk-management-and-compliance.ai-risk-register.view');
        Route::post('{aiRiskRegister}', 'update')->middleware('permission:risk-management-and-compliance.ai-risk-register.edit');
        Route::delete('{aiRiskRegister}', 'destroy')->middleware('permission:risk-management-and-compliance.ai-risk-register.delete');
    });

    Route::prefix('risk-methodologies')->controller(RiskMethodologyController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.risk-methodologies.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.risk-methodologies.create');
        Route::get('{riskMethodology}', 'show')->middleware('permission:risk-management-and-compliance.risk-methodologies.view');
        Route::post('{riskMethodology}', 'update')->middleware('permission:risk-management-and-compliance.risk-methodologies.edit');
        Route::delete('{riskMethodology}', 'destroy')->middleware('permission:risk-management-and-compliance.risk-methodologies.delete');
    });

    Route::prefix('ai-risk-treatments')->controller(AiRiskTreatmentController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.ai-risk-treatments.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.ai-risk-treatments.create');
        Route::get('{aiRiskTreatment}', 'show')->middleware('permission:risk-management-and-compliance.ai-risk-treatments.view');
        Route::post('{aiRiskTreatment}', 'update')->middleware('permission:risk-management-and-compliance.ai-risk-treatments.edit');
        Route::delete('{aiRiskTreatment}', 'destroy')->middleware('permission:risk-management-and-compliance.ai-risk-treatments.delete');
    });

    Route::prefix('kri-indicators')->controller(KriIndicatorController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.kri-indicators.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.kri-indicators.create');
        Route::get('{kriIndicator}', 'show')->middleware('permission:risk-management-and-compliance.kri-indicators.view');
        Route::post('{kriIndicator}', 'update')->middleware('permission:risk-management-and-compliance.kri-indicators.edit');
        Route::delete('{kriIndicator}', 'destroy')->middleware('permission:risk-management-and-compliance.kri-indicators.delete');
    });

    Route::prefix('record-of-processing-activities')->controller(RecordOfProcessingActivityController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:privacy-and-data-protection.record-of-processing-activities.view');
        Route::post('', 'store')->middleware('permission:privacy-and-data-protection.record-of-processing-activities.create');
        Route::get('{recordOfProcessingActivity}', 'show')->middleware('permission:privacy-and-data-protection.record-of-processing-activities.view');
        Route::post('{recordOfProcessingActivity}', 'update')->middleware('permission:privacy-and-data-protection.record-of-processing-activities.edit');
        Route::delete('{recordOfProcessingActivity}', 'destroy')->middleware('permission:privacy-and-data-protection.record-of-processing-activities.delete');
    });

    Route::prefix('data-subject-request-accesses')->controller(DataSubjectRequestAccessController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:privacy-and-data-protection.data-subject-request-accesses.view');
        Route::post('', 'store')->middleware('permission:privacy-and-data-protection.data-subject-request-accesses.create');
        Route::get('{dataSubjectRequestAccess}', 'show')->middleware('permission:privacy-and-data-protection.data-subject-request-accesses.view');
        Route::post('{dataSubjectRequestAccess}', 'update')->middleware('permission:privacy-and-data-protection.data-subject-request-accesses.edit');
        Route::delete('{dataSubjectRequestAccess}', 'destroy')->middleware('permission:privacy-and-data-protection.data-subject-request-accesses.delete');
    });

    Route::prefix('consent-records')->controller(ConsentRecordController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:core-assets.consent-records.view');
        Route::post('', 'store')->middleware('permission:core-assets.consent-records.create');
        Route::get('{consentRecord}', 'show')->middleware('permission:core-assets.consent-records.view');
        Route::post('{consentRecord}', 'update')->middleware('permission:core-assets.consent-records.edit');
        Route::delete('{consentRecord}', 'destroy')->middleware('permission:core-assets.consent-records.delete');
    });

    Route::prefix('data-protection-impact-assessments')->controller(DataProtectionImpactAssessmentController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:privacy-and-data-protection.data-protection-impact-assessments.view');
        Route::post('', 'store')->middleware('permission:privacy-and-data-protection.data-protection-impact-assessments.create');
        Route::get('{dataProtectionImpactAssessment}', 'show')->middleware('permission:privacy-and-data-protection.data-protection-impact-assessments.view');
        Route::post('{dataProtectionImpactAssessment}', 'update')->middleware('permission:privacy-and-data-protection.data-protection-impact-assessments.edit');
        Route::delete('{dataProtectionImpactAssessment}', 'destroy')->middleware('permission:privacy-and-data-protection.data-protection-impact-assessments.delete');
    });

    Route::prefix('privacy-incidents')->controller(PrivacyIncidentController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:privacy-and-data-protection.privacy-incidents.view');
        Route::post('', 'store')->middleware('permission:privacy-and-data-protection.privacy-incidents.create');
        Route::get('{privacyIncident}', 'show')->middleware('permission:privacy-and-data-protection.privacy-incidents.view');
        Route::post('{privacyIncident}', 'update')->middleware('permission:privacy-and-data-protection.privacy-incidents.edit');
        Route::delete('{privacyIncident}', 'destroy')->middleware('permission:privacy-and-data-protection.privacy-incidents.delete');
    });

    Route::prefix('/compliance-evidences')->controller(ComplianceEvidenceController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.compliance-evidences.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.compliance-evidences.create');
        Route::get('{complianceEvidence}', 'show')->middleware('permission:risk-management-and-compliance.compliance-evidences.view');
        Route::post('{complianceEvidence}', 'update')->middleware('permission:risk-management-and-compliance.compliance-evidences.edit');
        Route::delete('{complianceEvidence}', 'destroy')->middleware('permission:risk-management-and-compliance.compliance-evidences.delete');
    });

    Route::prefix('/regulatory-submissions')->controller(RegulatorySubmissionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:risk-management-and-compliance.regulatory-submissions.view');
        Route::post('', 'store')->middleware('permission:risk-management-and-compliance.regulatory-submissions.create');
        Route::get('{regulatorySubmission}', 'show')->middleware('permission:risk-management-and-compliance.regulatory-submissions.view');
        Route::post('{regulatorySubmission}', 'update')->middleware('permission:risk-management-and-compliance.regulatory-submissions.edit');
        Route::delete('{regulatorySubmission}', 'destroy')->middleware('permission:risk-management-and-compliance.regulatory-submissions.delete');
    });

    Route::prefix('/ai-committees')->controller(AiCommitteeController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.ai-committees.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.ai-committees.create');
        Route::get('{aiCommittee}', 'show')->middleware('permission:governance-and-oversight.ai-committees.view');
        Route::post('{aiCommittee}', 'update')->middleware('permission:governance-and-oversight.ai-committees.edit');
        Route::delete('{aiCommittee}', 'destroy')->middleware('permission:governance-and-oversight.ai-committees.delete');
    });

    Route::prefix('/committee-memberships')->controller(CommitteeMembershipController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.committee-memberships.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.committee-memberships.create');
        Route::get('{committeeMembership}', 'show')->middleware('permission:governance-and-oversight.committee-memberships.view');
        Route::post('{committeeMembership}', 'update')->middleware('permission:governance-and-oversight.committee-memberships.edit');
        Route::delete('{committeeMembership}', 'destroy')->middleware('permission:governance-and-oversight.committee-memberships.delete');
    });

    Route::prefix('/committee-meetings')->controller(CommitteeMeetingController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.committee-meetings.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.committee-meetings.create');
        Route::get('{committeeMeeting}', 'show')->middleware('permission:governance-and-oversight.committee-meetings.view');
        Route::post('{committeeMeeting}', 'update')->middleware('permission:governance-and-oversight.committee-meetings.edit');
        Route::delete('{committeeMeeting}', 'destroy')->middleware('permission:governance-and-oversight.committee-meetings.delete');
    });

    Route::prefix('/committee-decisions')->controller(CommitteeDecisionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.committee-decisions.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.committee-decisions.create');
        Route::get('{committeeDecision}', 'show')->middleware('permission:governance-and-oversight.committee-decisions.view');
        Route::post('{committeeDecision}', 'update')->middleware('permission:governance-and-oversight.committee-decisions.edit');
        Route::delete('{committeeDecision}', 'destroy')->middleware('permission:governance-and-oversight.committee-decisions.delete');
    });

    Route::prefix('/committee-actions')->controller(CommitteeActionController::class)->group(function () {
        Route::get('', 'index')->middleware('permission:governance-and-oversight.committee-actions.view');
        Route::post('', 'store')->middleware('permission:governance-and-oversight.committee-actions.create');
        Route::get('{committeeAction}', 'show')->middleware('permission:governance-and-oversight.committee-actions.view');
        Route::post('{committeeAction}', 'update')->middleware('permission:governance-and-oversight.committee-actions.edit');
        Route::delete('{committeeAction}', 'destroy')->middleware('permission:governance-and-oversight.committee-actions.delete');
    });

});
