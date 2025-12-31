<?php

use App\Models\Stakeholder;
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
        Schema::table('incident_actions', function (Blueprint $table) {
            $table->dropColumn('performed_by');
            $table->foreignIdFor(Stakeholder::class, 'performed_by')
                ->nullable()
                ->after('description')
                ->constrained()
                ->nullOnDelete();
            $table->string('execution_status')->nullable()->after('action_type');
            $table->string('individual_name')->nullable()->after('performed_by');
            $table->string('depends_on')->nullable()->after('individual_name');
            $table->string('approval_required')->nullable()->after('depends_on');
            $table->string('estimated_duration')->nullable()->after('approval_required');
            $table->string('actual_duration')->nullable()->after('estimated_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_actions', function (Blueprint $table) {
            if (Schema::hasColumn('incident_actions', 'performed_by')) {
                $table->dropForeign(['performed_by']);
                $table->dropColumn('performed_by');
            }

            $table->string('performed_by')->nullable()->after('description');

            if (Schema::hasColumn('incident_actions', 'execution_status')) {
                $table->dropColumn('execution_status');
            }

            if (Schema::hasColumn('incident_actions', 'individual_name')) {
                $table->dropColumn('individual_name');
            }

            if (Schema::hasColumn('incident_actions', 'depends_on')) {
                $table->dropColumn('depends_on');
            }

            if (Schema::hasColumn('incident_actions', 'approval_required')) {
                $table->dropColumn('approval_required');
            }

            if (Schema::hasColumn('incident_actions', 'estimated_duration')) {
                $table->dropColumn('estimated_duration');
            }

            if (Schema::hasColumn('incident_actions', 'actual_duration')) {
                $table->dropColumn('actual_duration');
            }
        });
    }
};
