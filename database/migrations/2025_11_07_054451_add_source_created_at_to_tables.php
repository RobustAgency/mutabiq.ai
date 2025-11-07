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
        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->timestamp('source_created_at')->nullable()->after('created_at');
        });

        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->timestamp('source_created_at')->nullable()->after('created_at');
        });

        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->timestamp('source_created_at')->nullable()->after('created_at');
        });

        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->timestamp('source_created_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->dropColumn('source_created_at');
        });

        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->dropColumn('source_created_at');
        });

        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->dropColumn('source_created_at');
        });

        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->dropColumn('source_created_at');
        });
    }
};
