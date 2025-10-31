<?php

use App\Models\Stakeholder;
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
        Schema::table('use_cases', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->text('business_objective')->nullable(false)->change();

            $table->foreignIdFor(Stakeholder::class, 'business_owner_id')->nullable()->constrained('stakeholders')->nullOnDelete();
            $table->foreignIdFor(Stakeholder::class, 'technical_owner_id')->nullable()->constrained('stakeholders')->nullOnDelete();

            // Add expected_roi_percentage
            $table->decimal('expected_roi_percentage', 5, 2)->nullable()->after('data_sensitivity');

            // Add budget_allocated
            $table->decimal('budget_allocated', 15, 2)->nullable()->after('expected_roi_percentage');

            // Rename go_live_date to target_go_live_date
            $table->renameColumn('go_live_date', 'target_go_live_date');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            // Drop old email columns
            $table->dropColumn(['business_owner_email', 'technical_owner_email']);

            // Add created_by and updated_by
            $table->string('created_by')->after('status');
            $table->string('updated_by')->nullable()->after('created_by');

            //Add priority and roi_classification
            $table->string('roi_classification')->nullable()->after('business_domain');

            $table->string('priority')->nullable()->after('roi_classification');

            // Add assessment boolean flags
            $table->boolean('roi_assessment')->nullable()->default(false)->after('updated_by');
            $table->boolean('risk_assessment')->nullable()->default(false)->after('roi_assessment');
            $table->boolean('data_assessment')->nullable()->default(false)->after('risk_assessment');

            // Rename and modify cost/revenue columns
            $table->renameColumn('implementation_cost', 'estimated_implementation_cost');
            $table->renameColumn('reduction_in_time', 'estimated_reduction_in_time');
            $table->renameColumn('reduction_in_cost', 'estimated_reduction_in_cost');
            $table->renameColumn('increase_in_revenue', 'estimated_revenue_increase');
            $table->renameColumn('fte_capacity_saved', 'estimated_fte_capacity_saving');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            // Change decimal types for estimated columns
            $table->decimal('estimated_implementation_cost', 15, 2)->nullable()->change();
            $table->decimal('estimated_reduction_in_time', 5, 2)->nullable()->change();
            $table->decimal('estimated_reduction_in_cost', 15, 2)->nullable()->change();
            $table->decimal('estimated_revenue_increase', 15, 2)->nullable()->change();

            // Rename data_readiness_level to data_readiness
            $table->renameColumn('data_readiness_level', 'data_readiness');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            // Drop columns that are not in the new schema
            $table->dropColumn([
                'expected_roi',
                'risk_avoidance',
                'use_case_type',
                'value_driver',
                'overall_risk_score',
                'human_oversight_mode',
                'dpia',
                'aia',
                'data_freshness',
                'regulatory_scope'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('use_cases', function (Blueprint $table) {
            // Restore old columns
            $table->float('expected_roi')->nullable();
            $table->integer('risk_avoidance')->nullable();
            $table->string('use_case_type')->nullable();
            $table->string('value_driver')->nullable();
            $table->integer('overall_risk_score')->nullable();
            $table->string('human_oversight_mode')->nullable();
            $table->boolean('dpia')->default(false);
            $table->boolean('aia')->default(false);
            $table->string('data_freshness')->nullable();
            $table->string('regulatory_scope')->nullable();
        });

        Schema::table('use_cases', function (Blueprint $table) {
            $table->renameColumn('data_readiness', 'data_readiness_level');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            $table->string('data_readiness_level')->change();

            // Revert estimated column names and types
            $table->renameColumn('estimated_implementation_cost', 'implementation_cost');
            $table->renameColumn('estimated_reduction_in_time', 'reduction_in_time');
            $table->renameColumn('estimated_reduction_in_cost', 'reduction_in_cost');
            $table->renameColumn('estimated_revenue_increase', 'increase_in_revenue');
            $table->renameColumn('estimated_fte_capacity_saving', 'fte_capacity_saved');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            $table->integer('implementation_cost')->nullable()->change();
            $table->float('reduction_in_time')->nullable()->change();
            $table->integer('reduction_in_cost')->nullable()->change();
            $table->integer('increase_in_revenue')->nullable()->change();
            $table->integer('fte_capacity_saved')->nullable()->change();

            // Drop assessment columns
            $table->dropColumn(['roi_assessment', 'risk_assessment', 'data_assessment']);

            // Drop created_by and updated_by
            $table->dropColumn(['created_by', 'updated_by']);

            // Restore old email columns
            $table->string('business_owner_email')->nullable();
            $table->string('technical_owner_email')->nullable();

            // Rename target_go_live_date back to go_live_date
            $table->renameColumn('target_go_live_date', 'go_live_date');
        });

        Schema::table('use_cases', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'budget_allocated',
                'expected_roi_percentage',
                'priority',
                'roi_classification'
            ]);

            // Drop foreign keys and columns
            $table->dropForeign(['business_owner_id']);
            $table->dropForeign(['technical_owner_id']);
            $table->dropColumn(['business_owner_id', 'technical_owner_id']);

            // Revert business_objective to nullable
            $table->text('business_objective')->nullable()->change();

            // Revert description to nullable
            $table->text('description')->nullable()->change();

            // Rename 'name' back to 'title'
            $table->renameColumn('name', 'title');
        });
    }
};
