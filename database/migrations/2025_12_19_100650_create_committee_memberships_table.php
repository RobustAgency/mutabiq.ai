<?php

use App\Models\AiCommittee;
use App\Models\Stakeholder;
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
        Schema::create('committee_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiCommittee::class)->constrained('ai_committees')->onDelete('cascade');
            $table->foreignIdFor(Stakeholder::class)->constrained('stakeholders')->onDelete('cascade');
            $table->string('eligibility');
            $table->string('member_role');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('expertise_tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_memberships');
    }
};
