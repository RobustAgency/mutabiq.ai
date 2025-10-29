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
        Schema::create('incident_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiIncident::class)->constrained('ai_incidents')->cascadeOnDelete();
            $table->string('audience_type');
            $table->string('channel');
            $table->text('notice_summary');
            $table->string('notice_link')->nullable();
            $table->dateTime('notified_at');
            $table->string('approved_by')->nullable();
            $table->string('approval_ref')->nullable();
            $table->boolean('follow_up_required');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_notifications');
    }
};
