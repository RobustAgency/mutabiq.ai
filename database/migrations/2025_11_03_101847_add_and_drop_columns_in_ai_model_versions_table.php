<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {
            $table->dropColumn([
                'compliance_check_status',
                'validation_status',
                'rollback_available',
                'performance_baseline_established',
            ]);

            if (!Schema::hasColumn('ai_model_versions', 'version_role')) {
                $table->string('version_role')->nullable()->after('version_type');
            }

            if (!Schema::hasColumn('ai_model_versions', 'version_source')) {
                $table->string('version_source')->nullable()->after('version_role');
            }

            if (!Schema::hasColumn('ai_model_versions', 'our_involvement')) {
                $table->string('our_involvement')->nullable()->after('version_source');
            }

            if (!Schema::hasColumn('ai_model_versions', 'customizations_applied')) {
                $table->json('customizations_applied')->nullable()->after('training_duration_hours');
            }

            if (!Schema::hasColumn('ai_model_versions', 'created_by')) {
                $table->string('created_by')->nullable()->after('has_performance_data');
            }

            if (!Schema::hasColumn('ai_model_versions', 'updated_by')) {
                $table->string('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {
            // Recreate the dropped columns
            if (!Schema::hasColumn('ai_model_versions', 'compliance_check_status')) {
                $table->string('compliance_check_status')->nullable();
            }

            if (!Schema::hasColumn('ai_model_versions', 'validation_status')) {
                $table->string('validation_status')->nullable();
            }

            if (!Schema::hasColumn('ai_model_versions', 'rollback_available')) {
                $table->boolean('rollback_available')->default(false);
            }

            if (!Schema::hasColumn('ai_model_versions', 'performance_baseline_established')) {
                $table->boolean('performance_baseline_established')->default(false);
            }

            // Drop the columns added in up()
            if (Schema::hasColumn('ai_model_versions', 'version_role')) {
                $table->dropColumn('version_role');
            }

            if (Schema::hasColumn('ai_model_versions', 'version_source')) {
                $table->dropColumn('version_source');
            }

            if (Schema::hasColumn('ai_model_versions', 'our_involvement')) {
                $table->dropColumn('our_involvement');
            }

            if (Schema::hasColumn('ai_model_versions', 'customizations_applied')) {
                $table->dropColumn('customizations_applied');
            }

            if (Schema::hasColumn('ai_model_versions', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('ai_model_versions', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
