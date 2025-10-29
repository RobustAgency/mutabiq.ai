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
        Schema::create('incident_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiIncident::class)->constrained('ai_incidents')->cascadeOnDelete();
            $table->string('action_type');
            $table->text('description');
            $table->string('performed_by');
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('validation_result');
            $table->text('validation_notes')->nullable();
            $table->string('linked_release_id')->nullable();
            $table->string('evidence_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_actions');
    }
};
