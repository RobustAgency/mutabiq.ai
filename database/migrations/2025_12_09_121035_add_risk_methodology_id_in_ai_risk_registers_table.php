<?php

use App\Models\RiskMethodology;
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
        Schema::table('ai_risk_registers', function (Blueprint $table) {
            $table->foreignIdFor(RiskMethodology::class)->nullable()->after('organization_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_risk_registers', function (Blueprint $table) {
            $table->dropForeign(['risk_methodology_id']);
            $table->dropColumn('risk_methodology_id');
        });
    }
};
