<?php

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
        Schema::table('ai_model_versions', function (Blueprint $table) {
            $table->dropColumn('customizations_applied');

            $table->json('customizations_applied')->nullable()->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {
            $table->dropColumn('customizations_applied');

            $table->string('customizations_applied')->nullable()->after('approval_status');
        });
    }
};
