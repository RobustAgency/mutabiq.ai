<?php

use App\Models\Stakeholder;
use App\Models\Vendor;
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
        Schema::table('ai_models', function (Blueprint $table) {
            if (Schema::hasColumn('ai_models', 'source_organization')) {
                $table->string('source_organization')->nullable()->change();
                $table->dropColumn('source_organization');
            }
        });

        Schema::table('ai_models', function (Blueprint $table) {
            $table->foreignIdFor(Stakeholder::class, 'source_organization_id')
                ->constrained('stakeholders')
                ->cascadeOnDelete()
                ->after('description');

            $table->foreignIdFor(Stakeholder::class, 'custodian_id')
                ->constrained('stakeholders')
                ->cascadeOnDelete()
                ->after('source_organization_id');

            $table->foreignIdFor(Vendor::class)
                ->nullable()
                ->constrained('vendors')
                ->nullOnDelete()
                ->after('custodian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            // Drop new foreign keys and columns
            $table->dropForeign(['source_organization_id']);
            $table->dropColumn('source_organization_id');

            $table->dropForeign(['custodian_id']);
            $table->dropColumn('custodian_id');

            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');

            // Restore the original column
            $table->string('source_organization')->after('description');
        });
    }
};
