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
        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->json('purpose')->change();
        });

        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->json('purpose')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consent_coverages', function (Blueprint $table) {
            $table->string('purpose')->change();
        });

        Schema::table('consent_scopes', function (Blueprint $table) {
            $table->string('purpose')->change();
        });
    }
};
