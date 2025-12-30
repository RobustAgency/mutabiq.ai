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
        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'source_created_at',
                'special_category_element_count',
                'masking_anonymization_method',
                'privacy_transform_evidence_ref',
            ]);

            $table->unsignedBigInteger('supersedes_snapshot_id')->nullable()->after('version_tag');
            $table->text('description')->nullable()->after('version_tag');
            $table->integer('file_count')->nullable()->after('row_count');
            $table->bigInteger('total_size')->nullable()->after('file_count');
            $table->string('size_unit')->nullable()->after('total_size');
            $table->string('file_format')->nullable()->after('size_unit');
            $table->integer('consent_coverage_at_creation')->nullable()->after('pii_element_count');
            $table->string('storage_tier')->nullable()->after('residency_zone');
            $table->string('compression')->nullable()->after('storage_tier');
            $table->string('encryption_status')->nullable()->after('compression');
            $table->string('masking_method_applied')->nullable()->after('encryption_status');
            $table->string('created_by_system')->nullable()->after('masking_method_applied');
            $table->string('approved_by')->nullable()->after('created_by_system');
            $table->date('expiration_date')->nullable()->after('approved_by');
            $table->string('status')->nullable()->after('expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dataset_snapshots', function (Blueprint $table) {
            $table->integer('special_category_element_count')->nullable();
            $table->string('masking_anonymization_method')->nullable();
            $table->string('privacy_transform_evidence_ref')->nullable();
            $table->dateTime('source_created_at')->nullable();

            $table->dropColumn([
                'supersedes_snapshot_id',
                'description',
                'file_count',
                'total_size',
                'size_unit',
                'file_format',
                'consent_coverage_at_creation',
                'storage_tier',
                'compression',
                'encryption_status',
                'masking_method_applied',
                'created_by_system',
                'approved_by',
                'expiration_date',
                'status',
            ]);
        });
    }
};
