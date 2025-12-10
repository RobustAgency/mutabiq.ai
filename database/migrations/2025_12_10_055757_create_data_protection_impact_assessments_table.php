<?php

use App\Models\User;
use App\Models\AiModel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_protection_impact_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('dpia_code')->unique();
            $table->string('dpia_name');
            $table->foreignIdFor(RecordOfProcessingActivity::class, 'ropa_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(AiModel::class, 'linked_ai_model_id')->nullable()->constrained()->onDelete('set null');
            $table->string('linked_asset_type');
            $table->boolean('automated_trigger');
            $table->string('trigger_reason');
            $table->string('risk_level');
            $table->integer('risk_score');
            $table->string('stage');
            $table->integer('completion_percentage')->default(0);
            $table->text('necessity_justification')->nullable();
            $table->text('proportionality_assessment');
            $table->text('alternatives_considered');
            $table->text('identified_risks')->nullable();
            $table->text('likelihood_assessment');
            $table->text('impact_assessment');
            $table->text('mitigation_measures')->nullable();
            $table->string('residual_risk_level')->nullable();
            $table->string('dpo_consulted')->nullable();
            $table->date('dpo_consultation_date')->nullable();
            $table->text('dpo_advice')->nullable();
            $table->foreignIdFor(User::class, 'dpo_user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('stakeholders_consulted')->nullable();
            $table->text('stakeholder_feedback')->nullable();
            $table->boolean('data_subjects_consulted')->default(false);
            $table->string('consultation_method')->nullable();
            $table->string('final_decision')->nullable();
            $table->date('approval_date')->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable()->constrained()->onDelete('set null');
            $table->text('conditions')->nullable();
            $table->string('status')->default('draft');
            $table->integer('review_frequency_months')->default(12);
            $table->date('next_review_date')->nullable();
            $table->json('applicable_jurisdictions');
            $table->foreignIdFor(User::class, 'created_by')->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class, 'updated_by')->constrained()->onDelete('cascade');
            $table->integer('version')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_protection_impact_assessments');
    }
};
