<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add organization_id to ai_model_versions
        Schema::table('ai_model_versions', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to ai_model_cards
        Schema::table('ai_model_cards', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to use_cases
        Schema::table('use_cases', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to ai_model_use_cases
        Schema::table('ai_model_use_cases', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to stakeholders
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to data_sources
        Schema::table('data_sources', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to datasets
        Schema::table('datasets', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to data_elements
        Schema::table('data_elements', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to dataset_element (pivot table)
        Schema::table('dataset_element', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to dataset_snapshots
        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to ai_model_dataset
        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to user_consents
        Schema::table('user_consents', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to consent_scopes
        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to consent_coverages
        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to dataset_subject_populations
        Schema::table('dataset_subject_populations', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });

        // Add organization_id to pdp_processing_registers
        Schema::table('pdp_processing_registers', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('ai_model_cards', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('ai_model_use_cases', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('data_elements', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('dataset_element', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('user_consents', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('dataset_subject_populations', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('pdp_processing_registers', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
