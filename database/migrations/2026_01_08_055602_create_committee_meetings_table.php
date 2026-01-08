<?php

use App\Models\AiCommittee;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('committee_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiCommittee::class)->constrained()->onDelete('cascade');
            $table->string('meeting_type');
            $table->timestamp('scheduled_at');
            $table->integer('duration_minutes')->nullable();
            $table->text('agenda');
            $table->string('materials_link')->nullable();
            $table->string('attendance_policy');
            $table->json('attendance_roster')->nullable();
            $table->string('minutes_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_meetings');
    }
};
