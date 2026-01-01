<?php

use App\Models\Dataset;
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
        Schema::table('corrective_preventive_actions', function (Blueprint $table) {
            $table->dropColumn([
                'source_id',
                'closed_at',
            ]);
            $table->string('source_reference')->nullable()->after('source_type');
            $table->foreignIdFor(Dataset::class)
                ->nullable()
                ->after('ai_model_id')
                ->constrained()
                ->nullOnDelete();
            $table->string('success_criteria')->nullable()->after('status');
            $table->string('linked_training')->nullable()->after('success_criteria');
            $table->string('estimated_cost')->nullable()->after('linked_training');
            $table->timestamp('effectiveness_review_date')->nullable()->after('estimated_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corrective_preventive_actions', function (Blueprint $table) {
            $table->dropForeign(['dataset_id']);
            $table->dropColumn([
                'source_reference',
                'dataset_id',
                'success_criteria',
                'linked_training',
                'estimated_cost',
                'effectiveness_review_date',
            ]);
            $table->string('source_id')->nullable()->after('source_type');
            $table->timestamp('closed_at')->nullable()->after('evidence_link');
        });
    }
};
