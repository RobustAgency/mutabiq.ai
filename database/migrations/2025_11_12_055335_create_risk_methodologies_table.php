<?php

use App\Models\Organization;
use App\Models\User;
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
        Schema::create('risk_methodologies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('likelihood_scale')->nullable();
            $table->json('impact_scale')->nullable();
            $table->json('matrix_rule')->nullable();
            $table->text('acceptance_thresholds')->nullable();
            $table->text('aggregation_logic')->nullable();
            $table->text('review_policy');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('owner_team');
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_methodologies');
    }
};
