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
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('source_ids'); // List of FK to data_source
            $table->string('purpose');
            $table->text('schema_summary')->nullable();
            $table->string('sensitivity');
            $table->string('contains_pii');
            $table->json('data_subject_categories'); // Multi-select
            $table->string('controller_role');

            // Lawful basis fields (required if PII)
            $table->string('lawful_basis')->nullable();
            $table->text('lawful_basis_detail')->nullable();

            // Consent fields (if basis=Consent)
            $table->boolean('consent_required')->default(false);
            $table->integer('consent_coverage_pct')->nullable();
            $table->string('consent_source_ref')->nullable();

            // Licensing
            $table->string('licensing_basis')->nullable();
            $table->string('license_type')->nullable();

            $table->string('privacy_notice_ref')->nullable();
            $table->string('cross_border_transfer');

            // Data characteristics
            $table->string('data_structure');
            $table->string('storage_format');
            $table->json('content_types')->nullable();

            $table->string('retention_policy_ref')->nullable();
            $table->string('dpia_ref')->nullable();
            $table->string('aia_ref')->nullable();
            $table->string('owner_team');
            $table->string('refresh_cadence')->nullable();
            $table->string('quality_SLA')->nullable();
            $table->string('catalog_asset_id')->nullable();
            $table->string('catalog_uri')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
