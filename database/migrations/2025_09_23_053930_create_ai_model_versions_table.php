<?php

use App\Models\AiModel;
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
        Schema::create('ai_model_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiModel::class)->constrained()->onDelete('cascade');
            $table->string('version_number');
            $table->text('description')->nullable();
            $table->string('version_type');
            $table->date('release_date')->nullable();
            $table->text('release_notes')->nullable();
            $table->string('architecture_type');
            $table->string('complexity_level');
            $table->bigInteger('parameter_count')->nullable();
            $table->json('input_modalities')->nullable();
            $table->json('output_modalities')->nullable();
            $table->string('model_file_size_gb');
            $table->integer('training_duration_hours')->nullable();
            $table->json('deployment_environments')->nullable();
            $table->string('deployment_status');
            $table->string('lifecycle_stage');
            $table->string('compliance_check_status')->default('not_checked');
            $table->string('validation_status');
            $table->boolean('rollback_available')->default(false);
            $table->boolean('has_performance_data')->default(false);
            $table->boolean('performance_baseline_established')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_versions');
    }
};
