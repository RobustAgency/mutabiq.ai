<?php

use App\Models\Stakeholder;
use App\Models\Vendor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop old column if exists
        Schema::table('ai_models', function (Blueprint $table) {
            if (Schema::hasColumn('ai_models', 'source_organization')) {
                $table->string('source_organization')->nullable()->change();
                $table->dropColumn('source_organization');
            }
        });

        // Step 2: Add new foreign keys only if they don't already exist
        Schema::table('ai_models', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_models', 'source_organization_id')) {
                $table->foreignIdFor(Stakeholder::class, 'source_organization_id')
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('description');
            }

            if (!Schema::hasColumn('ai_models', 'custodian_id')) {
                $table->foreignIdFor(Stakeholder::class, 'custodian_id')
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('source_organization_id');
            }

            if (!Schema::hasColumn('ai_models', 'vendor_id')) {
                $table->foreignIdFor(Vendor::class)
                    ->nullable()
                    ->constrained('vendors')
                    ->nullOnDelete()
                    ->after('custodian_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            if (Schema::hasColumn('ai_models', 'source_organization_id')) {
                $table->dropForeign(['source_organization_id']);
                $table->dropColumn('source_organization_id');
            }

            if (Schema::hasColumn('ai_models', 'custodian_id')) {
                $table->dropForeign(['custodian_id']);
                $table->dropColumn('custodian_id');
            }

            if (Schema::hasColumn('ai_models', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }

            if (!Schema::hasColumn('ai_models', 'source_organization')) {
                $table->string('source_organization')->after('description');
            }
        });
    }
};
