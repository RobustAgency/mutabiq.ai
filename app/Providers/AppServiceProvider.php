<?php

namespace App\Providers;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\AiAsset;
use App\Models\AiModel;
use App\Models\Agreement;
use App\Models\AiIncident;
use App\Models\AiCommittee;
use App\Models\AiModelCard;
use App\Models\AiModelDataset;
use App\Models\AiModelUseCase;
use App\Models\AiModelVersion;
use App\Models\AiRiskRegister;
use App\Clients\SupabaseClient;
use App\Models\AiModelArtifact;
use App\Models\AiRiskTreatment;
use App\Models\CommitteeAction;
use App\Models\ConsentCoverage;
use App\Models\CommitteeMeeting;
use App\Models\ArtifactAccessLog;
use App\Models\CommitteeDecision;
use App\Models\ComplianceEvidence;
use App\Observers\AiAssetObserver;
use App\Observers\AiModelObserver;
use App\Models\CommitteeMembership;
use App\Observers\AgreementObserver;
use App\Services\Auth\SupabaseGuard;
use Illuminate\Support\Facades\Auth;
use App\Observers\AiIncidentObserver;
use App\Observers\AiCommitteeObserver;
use App\Observers\AiModelCardObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\AiModelDatasetObserver;
use App\Observers\AiModelUseCaseObserver;
use App\Observers\AiModelVersionObserver;
use App\Observers\AiRiskRegisterObserver;
use App\Models\RecordOfProcessingActivity;
use App\Observers\AiModelArtifactObserver;
use App\Observers\AiRiskTreatmentObserver;
use App\Observers\CommitteeActionObserver;
use App\Observers\ConsentCoverageObserver;
use App\Observers\CommitteeMeetingObserver;
use App\Observers\ArtifactAccessLogObserver;
use App\Observers\CommitteeDecisionObserver;
use App\Observers\ComplianceEvidenceObserver;
use App\Observers\CommitteeMembershipObserver;
use App\Observers\RecordOfProcessingActivityObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SupabaseClient::class, function ($app) {
            return new SupabaseClient;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Supabase guard
        Auth::extend('supabase', function ($app, $name, array $config) {
            return new SupabaseGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(SupabaseClient::class)
            );
        });

        RecordOfProcessingActivity::observe(RecordOfProcessingActivityObserver::class);
        AiIncident::observe(AiIncidentObserver::class);
        Agreement::observe(AgreementObserver::class);
        AiAsset::observe(AiAssetObserver::class);
        AiCommittee::observe(AiCommitteeObserver::class);
        AiModel::observe(AiModelObserver::class);
        AiModelArtifact::observe(AiModelArtifactObserver::class);
        AiModelCard::observe(AiModelCardObserver::class);
        AiModelDataset::observe(AiModelDatasetObserver::class);
        AiModelUseCase::observe(AiModelUseCaseObserver::class);
        AiModelVersion::observe(AiModelVersionObserver::class);
        AiRiskRegister::observe(AiRiskRegisterObserver::class);
        AiRiskTreatment::observe(AiRiskTreatmentObserver::class);
        ArtifactAccessLog::observe(ArtifactAccessLogObserver::class);
        CommitteeAction::observe(CommitteeActionObserver::class);
        CommitteeDecision::observe(CommitteeDecisionObserver::class);
        CommitteeMeeting::observe(CommitteeMeetingObserver::class);
        CommitteeMembership::observe(CommitteeMembershipObserver::class);
        ComplianceEvidence::observe(ComplianceEvidenceObserver::class);
        ConsentCoverage::observe(ConsentCoverageObserver::class);

        // Set permission scope based on user's organization
        // This ensures users only see permissions scoped to their organization
        // unless they are a super admin
        Auth::resolved(function ($auth) {
            if (! Auth::guard('supabase')->check()) {
                return;
            }
            $user = Auth::guard('supabase')->user();
            if ($user instanceof User) {
                if ($user->role === UserRole::SUPER_ADMIN) {
                    return;
                }
                setPermissionsTeamId($user->organization_id);
            }
        });
    }
}
