<?php

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
        Schema::create('ai_model_use_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('business_domain');
            $table->string('business_owner_email');
            $table->string('technical_owner_email');
            $table->string('regulatory_scope');
            $table->string('data_sensitivity');
            $table->date('go_live_date')->nullable();
            $table->float('expected_roi')->nullable();
            $table->integer('implementation_cost')->nullable();
            $table->float('reduction_in_time')->nullable();
            $table->integer('reduction_in_cost')->nullable();
            $table->integer('increase_in_revenue')->nullable();
            $table->integer('risk_avoidance')->nullable();
            $table->integer('fte_capacity_saved')->nullable();
            $table->string('use_case_type');
            $table->string('value_driver');
            $table->string('risk_level');
            $table->integer('overall_risk_score')->nullable();
            $table->string('human_oversight_mode');
            $table->boolean('dpia')->default(false);
            $table->boolean('aia')->default(false);
            $table->string('data_availability_status');
            $table->string('data_readiness_level');
            $table->string('data_freshness');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_use_cases');
    }
};
