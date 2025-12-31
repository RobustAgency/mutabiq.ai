<?php

use App\Models\Dataset;
use App\Models\UseCase;
use App\Models\AiModelVersion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_incidents', function (Blueprint $table) {
            $table->dropForeign(['ai_model_version_id']);
            $table->dropForeign(['use_case_id']);
            $table->dropColumn([
                'category',
                'stage',
                'linked_release_id',
                'linked_capa_id',
                'ai_model_version_id',
                'use_case_id',
                'first_seen_at',
                'declared_at',
                'resolved_at',
                'closed_at',
                'impacted_data',
            ]);
            $table->string('incident_type')->nullable()->after('summary');
            $table->string('domain')->nullable()->after('incident_type');
            $table->renameColumn('ic_owner', 'incident_commander');
            $table->string('response_team')->nullable()->after('incident_commander');
            $table->string('primary_regulatory_framework')->nullable()->after('response_team');
            $table->string('notification_requirement')->nullable()->after('primary_regulatory_framework');
            $table->string('data_residency_affected')->nullable()->after('notification_requirement');
            $table->string('regulatory_reference')->nullable()->after('data_residency_affected');
            $table->renameColumn('impacted_users', 'estimated_impacted_users');
            $table->string('estimated_impacted_records')->nullable()->after('estimated_impacted_users');
            $table->string('estimated_impacted_users')->nullable()->change();
            $table->json('data_types_impacted')->nullable()->after('estimated_impacted_records');
            $table->json('affected_business_units')->nullable()->after('data_types_impacted');
            $table->json('external_parties_involved')->nullable()->after('affected_business_units');
            $table->text('business_impact_description')->nullable()->after('external_parties_involved');
            $table->foreignIdFor(Dataset::class, 'linked_dataset_id')->nullable()->after('business_impact_description')->constrained('datasets');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_incidents', function (Blueprint $table) {
            $table->dropForeign(['linked_dataset_id']);
            $table->dropColumn([
                'incident_type',
                'domain',
                'response_team',
                'primary_regulatory_framework',
                'notification_requirement',
                'data_residency_affected',
                'regulatory_reference',
                'data_types_impacted',
                'affected_business_units',
                'external_parties_involved',
                'business_impact_description',
                'linked_dataset_id',
                'estimated_impacted_records',
            ]);
            $table->renameColumn('incident_commander', 'ic_owner');
            $table->renameColumn('estimated_impacted_users', 'impacted_users');
            $table->json('impacted_data')->nullable()->after('impacted_users');
            $table->string('category')->nullable()->after('summary');
            $table->string('stage')->nullable()->after('status');
            $table->foreignIdFor(AiModelVersion::class)->nullable()->after('ai_model_id')->constrained('ai_model_versions')->nullOnDelete();
            $table->foreignIdFor(UseCase::class)->nullable()->after('ai_model_version_id')->constrained('use_cases')->nullOnDelete();
            $table->dateTime('first_seen_at')->nullable()->after('use_case_id');
            $table->dateTime('declared_at')->nullable()->after('first_seen_at');
            $table->dateTime('resolved_at')->nullable()->after('declared_at');
            $table->dateTime('closed_at')->nullable()->after('resolved_at');
            $table->string('linked_release_id')->nullable()->after('impacted_systems');
            $table->string('linked_capa_id')->nullable()->after('linked_assessment_id');
        });
    }
};
