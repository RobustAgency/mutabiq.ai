<?php

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
        Schema::create('consent_coverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->onDelete('cascade');
            $table->foreignId('snapshot_id')->nullable()->constrained('dataset_snapshots')->onDelete('set null');
            $table->string('purpose');
            $table->string('jurisdiction');
            $table->timestamp('as_of');
            $table->unsignedBigInteger('subjects_total');
            $table->unsignedBigInteger('subjects_with_valid_consent');
            $table->decimal('coverage_pct', 5, 2); // 0.00 to 100.00
            $table->string('evidence_ref');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_coverages');
    }
};
