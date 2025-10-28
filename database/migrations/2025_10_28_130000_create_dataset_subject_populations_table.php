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
        Schema::create('dataset_subject_populations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->onDelete('cascade');
            $table->foreignId('snapshot_id')->nullable()->constrained('dataset_snapshots')->onDelete('cascade');
            $table->string('subject_realm');
            $table->string('jurisdiction');
            $table->bigInteger('subjects_total')->unsigned();
            $table->timestamp('as_of');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_subject_populations');
    }
};
