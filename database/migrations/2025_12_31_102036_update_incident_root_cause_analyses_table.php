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
        Schema::table('incident_root_cause_analyses', function (Blueprint $table) {

            $table->dropColumn([
                'impact_assessment',
                'fixes_implemented',
                'lessons_learned',
            ]);

            $table->renameColumn('latent_causes', 'root_causes');
            $table->renameColumn('approved_by', 'lead_analyst');

            $table->timestamp('approved_at')->nullable()->change();

            $table->timestamp('analysis_date')->nullable()->after('rca_method');
            $table->text('control_failures')->nullable()->after('contributing_factors');
            $table->text('review_committee')->nullable()->after('lead_analyst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_root_cause_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'analysis_date',
                'control_failures',
                'review_committee',
            ]);

            $table->text('impact_assessment')->nullable()->after('contributing_factors');
            $table->text('fixes_implemented')->nullable()->after('impact_assessment');
            $table->text('lessons_learned')->nullable()->after('fixes_implemented');

            $table->renameColumn('root_causes', 'latent_causes');
            $table->renameColumn('lead_analyst', 'approved_by');
        });
    }
};
