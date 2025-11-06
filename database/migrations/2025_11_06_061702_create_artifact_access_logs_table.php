<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Stakeholder;
use App\Models\AiModelArtifact;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artifact_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiModelArtifact::class, 'artifact_id')->constrained('ai_model_artifacts')->onDelete('cascade');
            $table->foreignIdFor(Stakeholder::class, 'accessor_stakeholder_id')->constrained('stakeholders')->onDelete('cascade');
            $table->string('action');
            $table->string('context');
            $table->string('ts');
            $table->string('ip_or_agent')->nullable();
            $table->string('request_id')->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifact_access_logs');
    }
};
