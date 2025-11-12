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
        Schema::table('ai_model_artifacts', function (Blueprint $table) {
            $table->string('name')->nullable()->after('ai_model_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_artifacts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
