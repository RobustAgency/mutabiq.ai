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
        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn([
                'access_method',
                'classification',
                'service_model',
                'cloud_provider',
                'primary_region',
                'secondary_region',
                'network_ref',
                'retention_policy_ref',
                'catalog_uri',
            ]);
            $table->string('description')->nullable()->after('name');
            $table->string('criticality_level')->nullable()->after('residency');
            $table->string('technical_owner')->nullable()->after('hosting_model');
            $table->string('business_owner')->nullable()->after('technical_owner');
            $table->date('last_review_date')->nullable()->after('business_owner');
            $table->date('next_review_date')->nullable()->after('last_review_date');
            $table->string('status')->nullable()->after('next_review_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'criticality_level',
                'technical_owner',
                'business_owner',
                'last_review_date',
                'next_review_date',
                'status',
            ]);

            $table->string('access_method')->nullable()->after('owner_team');
            $table->string('classification')->nullable()->after('access_method');
            $table->string('service_model')->nullable()->after('classification');
            $table->string('cloud_provider')->nullable()->after('service_model');
            $table->string('primary_region')->nullable()->after('cloud_provider');
            $table->string('secondary_region')->nullable()->after('primary_region');
            $table->string('network_ref')->nullable()->after('secondary_region');
            $table->string('retention_policy_ref')->nullable()->after('network_ref');
            $table->string('catalog_uri')->nullable()->after('retention_policy_ref');
        });
    }
};
