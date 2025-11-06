<?php

use App\Models\AiModel;
use App\Models\Stakeholder;
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
        Schema::table('ai_model_cards', function (Blueprint $table) {
            // Drop columns that are no longer needed
            $table->dropColumn([
                'version',
                'access_level',
                'workflow_stage',
                'technical_review_status',
                'ethics_review_status',
                'compliance_review_status',
                'completeness_score',
                'latest_performance_date',
                'owner_email',
            ]);

            if (Schema::hasColumn('ai_model_cards', 'ai_model_id')) {
                $table->dropForeign(['ai_model_id']);
                $table->dropColumn('ai_model_id');
            }

            // Rename ai_model_version_id to version_id
            $table->renameColumn('ai_model_version_id', 'version_id');

            // Add new columns
            $table->foreignIdFor(Stakeholder::class, 'owner_stakeholder_id')
                ->after('creator_role')
                ->constrained('stakeholders')
                ->onDelete('cascade');

            $table->text('model_overview')->after('format');

            // Update organizational_context to be JSON
            $table->json('organizational_context')->nullable()->change();

            // Add created_by and updated_by
            $table->string('created_by')->after('next_review_date');
            $table->string('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_cards', function (Blueprint $table) {
            $table->foreignIdFor(AiModel::class)->constrained()->onDelete('cascade')->after('id');
            $table->string('version')->after('title');
            $table->string('access_level')->after('creator_role');
            $table->string('workflow_stage')->after('status');
            $table->string('technical_review_status')->after('workflow_stage');
            $table->string('ethics_review_status')->after('technical_review_status');
            $table->string('compliance_review_status')->after('ethics_review_status');
            $table->float('completeness_score')->default(0.0)->after('publication_status');
            $table->date('latest_performance_date')->nullable()->after('performance_summary');
            $table->string('owner_email')->after('access_level');

            // Drop new columns
            $table->dropForeign(['owner_stakeholder_id']);
            $table->dropColumn(['owner_stakeholder_id', 'model_overview', 'created_by', 'updated_by']);

            // Rename version_id back to ai_model_version_id
            $table->renameColumn('version_id', 'ai_model_version_id');

            // Change organizational_context back to text
            $table->text('organizational_context')->nullable()->change();
        });
    }
};
