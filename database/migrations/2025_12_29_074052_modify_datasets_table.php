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
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn([
                'data_structure',
                'storage_format',
                'content_types',
                'contains_pii',
                'retention_policy_ref',
                'dpia_ref',
                'aia_ref',
                'refresh_cadence',
                'quality_SLA',
                'catalog_asset_id',
                'catalog_uri',
                'privacy_notice_ref',
                'data_subject_categories',
                'controller_role',
                'lawful_basis',
                'lawful_basis_detail',
                'consent_required',
                'consent_coverage_pct',
                'consent_source_ref',
                'licensing_basis',
                'purpose',
            ]);
            $table->renameColumn('schema_summary', 'description');
            $table->string('purpose')->nullable()->after('description');
            $table->string('data_steward')->nullable()->after('owner_team');
            $table->string('status')->nullable()->after('source_ids');
            $table->bigInteger('estimated_row_count')->nullable()->after('status');
            $table->bigInteger('estimated_size')->nullable()->after('estimated_row_count');
            $table->string('size_unit')->nullable()->after('estimated_size');
            $table->string('retention_period')->nullable()->after('size_unit');
            $table->json('primary_languages')->nullable()->after('retention_period');
            $table->string('contains_personal_data')->nullable()->after('primary_languages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn([
                'purpose',
                'data_steward',
                'status',
                'estimated_row_count',
                'estimated_size',
                'size_unit',
                'retention_period',
                'primary_languages',
                'contains_personal_data',
            ]);
            $table->renameColumn('description', 'schema_summary');
            $table->json('purpose')->after('schema_summary');
            $table->string('contains_pii')->after('sensitivity');
            $table->json('data_subject_categories')->after('contains_pii');
            $table->string('controller_role')->after('data_subject_categories');
            $table->string('lawful_basis')->nullable()->after('controller_role');
            $table->text('lawful_basis_detail')->nullable()->after('lawful_basis');
            $table->boolean('consent_required')->default(false)->after('lawful_basis_detail');
            $table->integer('consent_coverage_pct')->nullable()->after('consent_required');
            $table->string('consent_source_ref')->nullable()->after('consent_coverage_pct');
            $table->string('licensing_basis')->nullable()->after('consent_source_ref');
            $table->string('privacy_notice_ref')->nullable()->after('license_type');
            $table->string('data_structure')->after('cross_border_transfer');
            $table->string('storage_format')->after('data_structure');
            $table->json('content_types')->nullable()->after('storage_format');
            $table->string('retention_policy_ref')->nullable()->after('content_types');
            $table->string('dpia_ref')->nullable()->after('retention_policy_ref');
            $table->string('aia_ref')->nullable()->after('dpia_ref');
            $table->string('refresh_cadence')->nullable()->after('owner_team');
            $table->string('quality_SLA')->nullable()->after('refresh_cadence');
            $table->string('catalog_asset_id')->nullable()->after('quality_SLA');
            $table->string('catalog_uri')->nullable()->after('catalog_asset_id');
        });
    }
};
