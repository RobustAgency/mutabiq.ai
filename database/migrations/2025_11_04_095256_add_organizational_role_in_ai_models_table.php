<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {

            if (!Schema::hasColumn('ai_models', 'organizational_role')) {
                $table->string('organizational_role')->after('domain_specialization');
            }

            if (Schema::hasColumn('ai_models', 'owner_stakeholder_id')) {
                // set nullable false
                $table->foreignId('owner_stakeholder_id')->nullable(false)->change();
            }

            if (!Schema::hasColumn('ai_models', 'creator_email')) {
                $table->string('creator_email')->after('vendor_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            if (Schema::hasColumn('ai_models', 'organizational_role')) {
                $table->dropColumn('organizational_role');
            }
            if (Schema::hasColumn('ai_models', 'owner_stakeholder_id')) {
                // set nullable true
                $table->foreignId('owner_stakeholder_id')->nullable()->change();
            }
            if (Schema::hasColumn('ai_models', 'creator_email')) {
                $table->dropColumn('creator_email');
            }
        });
    }
};
