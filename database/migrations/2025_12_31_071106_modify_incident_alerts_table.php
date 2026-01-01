<?php

use App\Models\DataSource;
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
        Schema::table('incident_alerts', function (Blueprint $table) {
            if (Schema::hasColumn('incident_alerts', 'rule_version')) {
                $table->dropColumn('rule_version');
            }
            $table->foreignIdFor(DataSource::class)
                ->nullable()
                ->after('source_type')
                ->constrained()
                ->nullOnDelete();

            $table->string('alert_sensitivity')->nullable()->after('source_ref');
            $table->boolean('auto_promote_incident')->default(false)->after('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_alerts', function (Blueprint $table) {
            $table->string('rule_version')->nullable();

            if (Schema::hasColumn('incident_alerts', 'data_source_id')) {
                $table->dropForeign(['data_source_id']);
                $table->dropColumn('data_source_id');
            }

            if (Schema::hasColumn('incident_alerts', 'alert_sensitivity')) {
                $table->dropColumn('alert_sensitivity');
            }

            if (Schema::hasColumn('incident_alerts', 'auto_promote_incident')) {
                $table->dropColumn('auto_promote_incident');
            }
        });
    }
};
