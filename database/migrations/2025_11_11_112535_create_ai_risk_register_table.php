<?php

use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\Organization;
use App\Models\Stakeholder;
use App\Models\UseCase;
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
        Schema::create('ai_risk_register', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained('organizations')->cascadeOnDelete();

            // Core risk fields
            $table->string('title');
            $table->string('risk_category');
            $table->foreignIdFor(AiModel::class)->constrained('ai_models')->cascadeOnDelete();
            $table->foreignIdFor(AiModelVersion::class)->nullable()->constrained('ai_model_versions')->nullOnDelete();
            $table->foreignIdFor(UseCase::class)->nullable()->constrained('use_cases')->nullOnDelete();
            $table->text('description');

            // Related controls (stored as JSON array of IDs/refs)
            $table->json('related_controls')->nullable();
            $table->string('likelihood_code');
            $table->string('impact_code');
            $table->string('inherent_score')->nullable();
            $table->string('residual_score')->nullable();
            $table->string('risk_level');

            // Risk management
            $table->string('decision');
            $table->foreignIdFor(Stakeholder::class, 'risk_owner')->constrained('stakeholders')->cascadeOnDelete();
            $table->string('review_cadence');
            $table->date('next_review_due');
            $table->string('status');

            // Linkages
            $table->unsignedBigInteger('linked_assessment_id')->nullable();
            $table->unsignedBigInteger('linked_incident_id')->nullable();
            $table->unsignedBigInteger('linked_capa_id')->nullable();

            // Evidence
            $table->string('evidence_link')->nullable();

            // Immutable snapshots
            $table->string('likelihood_label_snapshot')->nullable();
            $table->string('impact_label_snapshot')->nullable();
            $table->string('method_name_snapshot')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_risk_register');
    }
};
