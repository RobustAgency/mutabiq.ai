<?php

use App\Models\AiModel;
use App\Models\Control;
use App\Models\UseCase;
use App\Models\CommitteeMeeting;
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
        Schema::create('committee_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CommitteeMeeting::class)->constrained()->onDelete('cascade');
            $table->string('decision_type');
            $table->string('decision_scope');
            $table->foreignIdFor(AiModel::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(UseCase::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Control::class)->nullable()->constrained()->onDelete('set null');
            $table->string('related_ref')->nullable();
            $table->text('rationale');
            $table->text('conditions')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('vote_method');
            $table->string('vote_result');
            $table->string('owner_team');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_decisions');
    }
};
