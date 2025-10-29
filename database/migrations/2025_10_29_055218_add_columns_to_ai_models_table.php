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
            $table->dropColumn('source_organization');
            $table->foreignIdFor(Stakeholder::class, 'source_organization_id')->constrained('stakeholders')->cascadeOnDelete()->after('description');
            $table->foreignIdFor(Stakeholder::class, 'custodian_id')->constrained('stakeholders')->cascadeOnDelete()->after('source_organization_id');
            $table->foreignIdFor(Vendor::class)->nullable()->constrained('vendors')->onDelete('set null')->after('custodian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->string('source_organization')->after('description');
            $table->dropForeign(['source_organization_id']);
            $table->dropColumn('source_organization_id');
            $table->dropForeign(['custodian_id']);
            $table->dropColumn('custodian_id');
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
