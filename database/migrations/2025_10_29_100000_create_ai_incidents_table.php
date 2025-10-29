<?php

use App\Models\AiModel;
use App\Models\AiModelVersion;
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
        Schema::create('ai_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->string('category');
            $table->string('severity');
            $table->string('status');
            $table->string('stage');
            $table->string('ic_owner');
            $table->foreignIdFor(AiModel::class)->nullable()->constrained('ai_models')->nullOnDelete();
            $table->foreignIdFor(AiModelVersion::class)->nullable()->constrained('ai_model_versions')->nullOnDelete();
            $table->foreignIdFor(UseCase::class)->nullable()->constrained('use_cases')->nullOnDelete();
            $table->dateTime('first_seen_at');
            $table->dateTime('declared_at');
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->string('impacted_users')->nullable();
            $table->json('impacted_data');
            $table->text('impacted_systems')->nullable();
            $table->string('linked_release_id')->nullable();
            $table->string('linked_risk_id')->nullable();
            $table->string('linked_assessment_id')->nullable();
            $table->string('linked_capa_id')->nullable();
            $table->string('evidence_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_incidents');
    }
};
