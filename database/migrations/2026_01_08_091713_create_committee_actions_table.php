<?php

use App\Models\Stakeholder;
use App\Models\CommitteeDecision;
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
        Schema::create('committee_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CommitteeDecision::class)->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('action_type');
            $table->foreignIdFor(Stakeholder::class, 'assignee_id')->constrained('stakeholders')->onDelete('cascade');
            $table->date('due_date');
            $table->string('status');
            $table->string('verification_result');
            $table->string('evidence_link')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_actions');
    }
};
