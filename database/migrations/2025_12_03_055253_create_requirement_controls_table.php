<?php

use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
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
        Schema::create('requirement_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Requirement::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AiModel::class)->nullable()->constrained()->nullOnDelete();
            $table->string('coverage');
            $table->text('interpretation_notes')->nullable();
            $table->text('residual_gaps')->nullable();
            $table->string('review_status')->nullable();
            $table->foreignIdFor(User::class, 'reviewed_by')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['requirement_id', 'control_id', 'ai_model_id'], 'requirement__control_model_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_controls');
    }
};
