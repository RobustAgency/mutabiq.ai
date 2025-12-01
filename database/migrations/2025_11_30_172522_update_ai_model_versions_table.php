<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn([
                'version_role',
                'version_source',
                'has_performance_data',
                'created_by',
                'updated_by',
            ]);

            // Rename column
            $table->renameColumn('our_involvement', 'org_involvement');

            // Add new columns
            $table->string('release_role')->nullable()->after('org_involvement');
            $table->string('source_type')->nullable()->after('release_role');
            $table->string('approval_status')->nullable()->after('source_type');

            // Recreate created_by & updated_by as foreign keys
            $table->unsignedBigInteger('created_by')->nullable()->after('source_type');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ai_model_versions', function (Blueprint $table) {

            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);

            // Restore removed columns
            $table->string('version_role')->nullable()->after('org_involvement');
            $table->string('version_source')->nullable()->after('version_role');
            $table->boolean('has_performance_data')->default(false)->after('customizations_applied');
            $table->string('created_by')->nullable()->after('has_performance_data');
            $table->string('updated_by')->nullable()->after('created_by');

            // Revert rename
            $table->renameColumn('org_involvement', 'our_involvement');

            // Remove added columns
            $table->dropColumn(['release_role', 'source_type', 'approval_status']);
        });
    }
};
