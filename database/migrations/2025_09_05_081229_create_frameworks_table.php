<?php

use App\Models\User;
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
        Schema::create('frameworks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('geography');
            $table->string('category');
            $table->string('version');
            $table->date('release_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->text('description')->nullable();

            // Additional Information
            $table->string('authority_publisher')->nullable();
            $table->string('binding_level')->nullable();
            $table->string('sector_applicability')->nullable();
            $table->string('risk_class_coverage')->nullable();
            $table->string('certification_attestation')->nullable();
            $table->string('assessment_mode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frameworks');
    }
};
