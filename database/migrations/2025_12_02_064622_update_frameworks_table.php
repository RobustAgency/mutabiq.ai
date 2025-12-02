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
        Schema::table('frameworks', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'type',
                'geography',
                'category',
                'release_date',
                'is_published',
                'description',
                'authority_publisher',
                'binding_level',
                'sector_applicability',
                'risk_class_coverage',
                'certification_attestation',
                'assessment_mode',
            ]);
            $table->json('jurisdictions')->nullable()->after('version');
            $table->text('scope')->nullable()->after('jurisdictions');
            $table->string('status')->nullable()->after('scope');
            $table->date('effective_date')->nullable()->after('status');
            $table->string('source_url')->nullable()->after('effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->dropColumn([
                'jurisdictions',
                'scope',
                'status',
                'effective_date',
                'source_url',
            ]);
            $table->string('code')->nullable()->after('name')->unique();
            $table->string('type')->nullable()->after('code');
            $table->string('geography')->nullable()->after('type');
            $table->string('category')->nullable()->after('geography');
            $table->date('release_date')->nullable()->after('version');
            $table->boolean('is_published')->default(false)->after('release_date');
            $table->text('description')->nullable()->after('is_published');
            $table->string('authority_publisher')->nullable()->after('description');
            $table->string('binding_level')->nullable()->after('authority_publisher');
            $table->string('sector_applicability')->nullable()->after('binding_level');
            $table->string('risk_class_coverage')->nullable()->after('sector_applicability');
            $table->string('certification_attestation')->nullable()->after('risk_class_coverage');
            $table->string('assessment_mode')->nullable()->after('certification_attestation');
        });
    }
};
