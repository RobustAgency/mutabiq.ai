<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('use_cases', function (Blueprint $table) {

            $table->dropColumn([
                'business_objective',
                'roi_assessment',
                'risk_assessment',
                'data_assessment',
                'risk_level',
                'estimated_implementation_cost',
                'estimated_reduction_in_time',
                'estimated_reduction_in_cost',
                'estimated_revenue_increase',
                'created_by',
                'updated_by',
            ]);

            $table->renameColumn('target_go_live_date', 'target_deployment_date');
            $table->renameColumn('estimated_fte_capacity_saving', 'estimated_fte_saving');
            $table->renameColumn('expected_roi_percentage', 'expected_roi');

            $table->string('business_domain')->nullable()->change();
            $table->string('status')->nullable()->change();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->text('problem_statement')->after('description')->nullable();
            $table->text('expected_business_value')->after('problem_statement')->nullable();
            $table->decimal('estimated_time_savings', 10, 2)->after('expected_roi')->nullable();
            $table->decimal('estimated_cost_savings', 10, 2)->after('estimated_time_savings')->nullable();
            $table->decimal('estimated_revenue_impact', 10, 2)->after('estimated_cost_savings')->nullable();
            $table->text('success_metrics')->after('estimated_revenue_impact')->nullable();
            $table->string('preliminary_risk_level')->after('success_metrics')->nullable();
            $table->text('regulatory_impact')->after('preliminary_risk_level')->nullable();
            $table->text('potential_harm')->after('regulatory_impact')->nullable();
            $table->text('human_oversight_mode')->after('potential_harm')->nullable();
            $table->text('dependencies')->after('human_oversight_mode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('use_cases', function (Blueprint $table) {

            $table->text('business_objective')->nullable();
            $table->text('roi_assessment')->nullable();
            $table->text('risk_assessment')->nullable();
            $table->text('data_assessment')->nullable();
            $table->string('risk_level')->nullable();
            $table->decimal('estimated_implementation_cost', 10, 2)->nullable();
            $table->decimal('estimated_reduction_in_time', 10, 2)->nullable();
            $table->decimal('estimated_reduction_in_cost', 10, 2)->nullable();
            $table->decimal('estimated_revenue_increase', 10, 2)->nullable();

            $table->renameColumn('target_deployment_date', 'target_go_live_date');
            $table->renameColumn('estimated_fte_saving', 'estimated_fte_capacity_saving');
            $table->renameColumn('expected_roi', 'expected_roi_percentage');

            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->dropColumn([
                'problem_statement',
                'expected_business_value',
                'estimated_time_savings',
                'estimated_cost_savings',
                'estimated_revenue_impact',
                'success_metrics',
                'preliminary_risk_level',
                'regulatory_impact',
                'potential_harm',
                'human_oversight_mode',
                'dependencies',
            ]);
        });
    }
};
