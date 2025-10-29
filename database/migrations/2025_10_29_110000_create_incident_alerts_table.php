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
        Schema::create('incident_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiIncident::class)->constrained('ai_incidents')->cascadeOnDelete();
            $table->string('source_type');
            $table->string('source_ref')->nullable();
            $table->string('rule_version')->nullable();
            $table->text('context')->nullable();
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at')->nullable();
            $table->string('evidence_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_alerts');
    }
};
