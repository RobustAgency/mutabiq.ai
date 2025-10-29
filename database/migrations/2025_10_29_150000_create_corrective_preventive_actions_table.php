<?php

use App\Models\AiModel;
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
        Schema::create('corrective_preventive_actions', function (Blueprint $table) {
            $table->id();
            $table->string('source_type')->default('incident');
            $table->string('source_id');
            $table->foreignIdFor(AiModel::class)->nullable()->constrained('ai_models')->onDelete('set null');
            $table->string('title');
            $table->string('capa_type');
            $table->string('priority');
            $table->string('owner_team');
            $table->string('assignee')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('actions')->nullable();
            $table->date('due_date');
            $table->string('status')->default('new');
            $table->string('verification_result')->nullable();
            $table->string('evidence_link')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_preventive_actions');
    }
};
