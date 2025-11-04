<?php

use App\Models\Organization;
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
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('agreements', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('ai_assets', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('ai_incidents', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('incident_alerts', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('incident_actions', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('incident_root_cause_analyses', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('corrective_preventive_actions', function (Blueprint $table) {
            $table->foreignIdFor(Organization::class)->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('agreements', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('ai_assets', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('ai_incidents', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('incident_alerts', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('incident_actions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('incident_root_cause_analyses', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('corrective_preventive_actions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
