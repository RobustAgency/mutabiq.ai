<?php

use App\Models\AiIncident;
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
        Schema::create('incident_root_cause_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiIncident::class)->constrained('ai_incidents')->cascadeOnDelete();
            $table->string('rca_method');
            $table->text('immediate_cause');
            $table->text('latent_causes');
            $table->text('contributing_factors')->nullable();
            $table->text('impact_assessment')->nullable();
            $table->text('fixes_implemented')->nullable();
            $table->text('lessons_learned');
            $table->text('recommendations');
            $table->string('approved_by');
            $table->dateTime('approved_at');
            $table->string('report_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_root_cause_analyses');
    }
};
