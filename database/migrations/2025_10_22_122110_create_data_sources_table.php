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
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_type');
            $table->string('owner_team');
            $table->json('data_domains');
            $table->string('access_method');
            $table->string('residency');
            $table->string('classification');
            $table->string('hosting_model');
            $table->string('service_model');
            $table->string('cloud_provider');
            $table->string('primary_region')->nullable();
            $table->string('secondary_region')->nullable();
            $table->string('network_ref')->nullable();
            $table->string('retention_policy_ref')->nullable();
            $table->string('catalog_uri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};
