<?php

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
        Schema::create('record_of_processing_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code')->unique();
            $table->string('activity_name');
            $table->string('purpose');
            $table->text('detailed_purpose')->nullable();
            $table->string('owner_team');
            $table->string('controller_role');
            $table->json('data_subject_categories');
            $table->json('data_categories');
            $table->boolean('contains_pii')->nullable();
            $table->boolean('consent_required')->nullable();
            $table->string('lawful_basis');
            $table->string('legitimate_interest_assessment')->nullable();
            $table->integer('consent_coverage_percent')->nullable();
            $table->boolean('dpia_required')->nullable();
            $table->string('dpia_status')->nullable();
            $table->unsignedBigInteger('dpia_id')->nullable();
            $table->string('retention_period');
            $table->text('retention_justification');
            $table->boolean('has_international_transfers')->nullable();
            $table->json('applicable_jurisdictions');
            $table->json('linked_dataset_ids')->nullable();
            $table->json('linked_ai_models_ids')->nullable();
            $table->text('security_measures');
            $table->json('internal_recipients')->nullable();
            $table->json('external_recipients')->nullable();
            $table->string('status');
            $table->date('last_reviewed_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_of_processing_activities');
    }
};
