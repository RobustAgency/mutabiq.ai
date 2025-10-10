<?php

use App\Models\AiModel;
use App\Models\AiModelVersion;
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
        Schema::create('ai_model_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiModel::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(AiModelVersion::class)->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('version');
            $table->string('creator_role');
            $table->string('owner_email');
            $table->string('format');
            $table->string('access_level');
            $table->string('status');
            $table->string('workflow_stage');
            $table->string('technical_review_status');
            $table->string('ethics_review_status');
            $table->string('compliance_review_status');
            $table->string('publication_status');
            $table->float('completeness_score')->default(0.0);
            $table->text('organizational_context')->nullable();
            $table->text('intended_use')->nullable();
            $table->text('training_data_overview')->nullable();
            $table->text('bias_evaluation_methods')->nullable();
            $table->text('model_limitations')->nullable();
            $table->text('ethical_considerations')->nullable();
            $table->text('risk_summary')->nullable();
            $table->text('performance_summary')->nullable();
            $table->date('latest_performance_date')->nullable();
            $table->date('publication_date')->nullable();
            $table->date('last_review_date')->nullable();
            $table->date('next_review_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_cards');
    }
};
