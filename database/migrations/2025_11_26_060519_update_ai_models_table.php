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
        Schema::table('ai_models', function (Blueprint $table) {
            // Drop foreign keys first (using column names for SQLite compatibility)
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['source_org_stakeholder_id']);
            $table->dropForeign(['owner_stakeholder_id']);
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Drop columns after foreign keys are removed
            $table->dropColumn([
                'description',
                'creator_email',
                'operational_status',
                'development_source',
                'vendor_id',
                'source_org_stakeholder_id',
                'owner_stakeholder_id',
            ]);
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Rename columns
            $table->renameColumn('primary_category', 'category');
            $table->renameColumn('domain_specialization', 'technical_domain');
            $table->renameColumn('business_status', 'business_adoption_status');
            $table->renameColumn('ownership_type', 'ownership_category');
            $table->renameColumn('organizational_role', 'responsible_org_role');
            $table->renameColumn('regulatory_risk_classification', 'regulatory_risk_tier');
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Make technical_domain nullable
            $table->string('technical_domain')->nullable()->change();
            $table->string('business_adoption_status')->nullable()->change();
            $table->string('regulatory_risk_tier')->nullable()->change();
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Add new columns after renames
            $table->text('purpose')->nullable()->after('technical_domain');
            $table->string('criticality_level')->nullable()->after('purpose');
            $table->string('eu_ai_category')->nullable()->after('regulatory_risk_tier');
            $table->unsignedBigInteger('business_owner_id')->nullable()->after('responsible_org_role');
            $table->unsignedBigInteger('custodian_id')->nullable()->after('business_owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            // Drop added columns
            $table->dropColumn([
                'purpose',
                'criticality_level',
                'eu_ai_category',
                'business_owner_id',
                'custodian_id',
            ]);
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Reverse column renames
            $table->renameColumn('category', 'primary_category');
            $table->renameColumn('technical_domain', 'domain_specialization');
            $table->renameColumn('business_adoption_status', 'business_status');
            $table->renameColumn('ownership_category', 'ownership_type');
            $table->renameColumn('responsible_org_role', 'organizational_role');
            $table->renameColumn('regulatory_risk_tier', 'regulatory_risk_classification');
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Restore dropped columns
            $table->string('creator_email')->nullable();
            $table->string('operational_status')->nullable();
            $table->string('development_source')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('source_org_stakeholder_id')->nullable();
            $table->unsignedBigInteger('owner_stakeholder_id')->nullable();
        });

        Schema::table('ai_models', function (Blueprint $table) {
            // Restore foreign keys
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('source_org_stakeholder_id')->references('id')->on('stakeholders')->onDelete('cascade');
            $table->foreign('owner_stakeholder_id')->references('id')->on('stakeholders')->onDelete('cascade');
        });
    }
};
