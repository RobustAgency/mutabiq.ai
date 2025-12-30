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
        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->dropColumn([
                'access_path',
                'transform_pack_link',
                'license_check_ref',
                'privacy_check_ref',
                'eligibility_status',
                'notes',
                'source_created_at',
            ]);
            $table->bigInteger('rows_used')->nullable()->after('role');
            $table->date('training_start_date')->nullable()->after('rows_used');
            $table->date('training_end_date')->nullable()->after('training_start_date');
            $table->string('training_duration')->nullable()->after('training_end_date');
            $table->string('compute_resources')->nullable()->after('training_duration');
            $table->decimal('cost', 15, 2)->nullable()->after('compute_resources');
            $table->string('consent_check_status')->nullable()->after('cost');
            $table->string('cross_border_check')->nullable()->after('consent_check_status');
            $table->string('special_category_check')->nullable()->after('cross_border_check');
            $table->boolean('bias_mitigation_applied')->nullable()->after('special_category_check');
            $table->string('created_by_system')->nullable()->after('bias_mitigation_applied');
            $table->string('linkage_status')->nullable()->after('created_by_system');
            $table->text('business_justification')->nullable()->after('linkage_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_dataset', function (Blueprint $table) {
            $table->string('access_path')->nullable()->after('dataset_snapshot_id');
            $table->string('transform_pack_link')->nullable()->after('access_path');
            $table->string('license_check_ref')->nullable()->after('transform_pack_link');
            $table->string('privacy_check_ref')->nullable()->after('license_check_ref');
            $table->string('eligibility_status')->nullable()->after('privacy_check_ref');
            $table->text('notes')->nullable()->after('eligibility_status');
            $table->dateTime('source_created_at')->nullable()->after('notes');

            $table->dropColumn([
                'rows_used',
                'training_start_date',
                'training_end_date',
                'training_duration',
                'compute_resources',
                'cost',
                'consent_check_status',
                'cross_border_check',
                'special_category_check',
                'bias_mitigation_applied',
                'created_by_system',
                'linkage_status',
                'business_justification',
            ]);
        });
    }
};
