<?php

use App\Models\Dataset;
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
        Schema::create('consent_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dataset::class)->constrained('datasets')->onDelete('cascade');
            $table->string('purpose');
            $table->string('subject_realm');
            $table->string('jurisdiction');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_scopes');
    }
};
