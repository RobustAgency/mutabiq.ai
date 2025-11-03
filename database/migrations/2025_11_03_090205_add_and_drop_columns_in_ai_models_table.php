<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Stakeholder;
use App\Models\AiModelVersion;

return new class extends Migration
{
    public function up(): void
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

            $table->dropColumn([
                'total_versions',
                'strategic_importance',
                'organizational_role',
                'current_owner',
            ]);

            if (!Schema::hasColumn('ai_models', 'source_org_stakeholder_id')) {
                $table->foreignIdFor(Stakeholder::class, 'source_org_stakeholder_id')
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('vendor_id');
            }

            if (!Schema::hasColumn('ai_models', 'owner_stakeholder_id')) {
                $table->foreignIdFor(Stakeholder::class, 'owner_stakeholder_id')
                    ->nullable()
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('source_org_stakeholder_id');
            }

            if (!Schema::hasColumn('ai_models', 'current_version_id')) {
                $table->foreignIdFor(AiModelVersion::class, 'current_version_id')
                    ->nullable()
                    ->constrained('ai_model_versions')
                    ->nullOnDelete()
                    ->after('owner_stakeholder_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {

            if (Schema::hasColumn('ai_models', 'source_org_stakeholder_id')) {
                $table->dropForeign(['source_org_stakeholder_id']);
                $table->dropColumn('source_org_stakeholder_id');
            }

            if (Schema::hasColumn('ai_models', 'owner_stakeholder_id')) {
                $table->dropForeign(['owner_stakeholder_id']);
                $table->dropColumn('owner_stakeholder_id');
            }

            if (Schema::hasColumn('ai_models', 'current_version_id')) {
                $table->dropForeign(['current_version_id']);
                $table->dropColumn('current_version_id');
            }

            if (!Schema::hasColumn('ai_models', 'source_organization_id')) {
                $table->foreignIdFor(Stakeholder::class, 'source_organization_id')
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('description');
            }

            if (!Schema::hasColumn('ai_models', 'custodian_id')) {
                $table->foreignIdFor(Stakeholder::class, 'custodian_id')
                    ->nullable()
                    ->constrained('stakeholders')
                    ->cascadeOnDelete()
                    ->after('source_organization_id');
            }

            if (!Schema::hasColumn('ai_models', 'total_versions')) {
                $table->integer('total_versions')->nullable()->after('custodian_id');
            }

            if (!Schema::hasColumn('ai_models', 'strategic_importance')) {
                $table->string('strategic_importance')->nullable()->after('total_versions');
            }

            if (!Schema::hasColumn('ai_models', 'organizational_role')) {
                $table->string('organizational_role')->nullable()->after('strategic_importance');
            }

            if (!Schema::hasColumn('ai_models', 'current_owner')) {
                $table->string('current_owner')->nullable()->after('organizational_role');
            }
        });
    }
};
