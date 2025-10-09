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
        Schema::table('ai_model_use_cases', function (Blueprint $table) {
            $table->json('regulatory_scope')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_use_cases', function (Blueprint $table) {
            $table->string('regulatory_scope')->change();
        });
    }
};
