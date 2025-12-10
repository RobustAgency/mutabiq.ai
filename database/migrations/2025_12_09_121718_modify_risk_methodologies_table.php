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
        Schema::table('risk_methodologies', function (Blueprint $table) {
            $table->dropColumn(['likelihood_scale', 'impact_scale']);
        });

        Schema::table('risk_methodologies', function (Blueprint $table) {
            $table->string('likelihood_scale')->nullable()->after('name');
            $table->string('impact_scale')->nullable()->after('likelihood_scale');
        });
    }

    public function down(): void
    {
        Schema::table('risk_methodologies', function (Blueprint $table) {
            $table->dropColumn(['likelihood_scale', 'impact_scale']);
        });

        Schema::table('risk_methodologies', function (Blueprint $table) {
            $table->json('likelihood_scale')->nullable()->after('name');
            $table->json('impact_scale')->nullable()->after('likelihood_scale');
        });
    }
};
