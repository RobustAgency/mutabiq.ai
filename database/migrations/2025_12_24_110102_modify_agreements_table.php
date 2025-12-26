<?php

use App\Models\User;
use App\Models\Stakeholder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agreements', function ($table) {
            $table->foreignIdFor(Stakeholder::class, 'agreement_owner_id')->nullable()->after('status')->constrained('stakeholders')->nullOnDelete();
            $table->json('asset_types_covered')->nullable()->after('agreement_owner_id');
            $table->string('renewal_type')->nullable()->after('asset_types_covered');
            $table->integer('notice_period_days')->nullable()->after('renewal_type');
            $table->string('termination_for_convenience')->default(false)->after('notice_period_days');
            $table->string('governing_law')->nullable()->after('termination_for_convenience');
            $table->string('sub_processing_rights')->nullable()->after('transfer_mechanism');
            $table->string('contract_value')->nullable()->after('sub_processing_rights');
            $table->string('liability_cap')->nullable()->after('contract_value');
            $table->string('insurance_requirements')->nullable()->after('liability_cap');
            $table->string('indemnification')->nullable()->after('insurance_requirements');
            $table->string('internal_reference_number')->nullable()->after('indemnification');
            $table->string('vendor_contract_id')->nullable()->after('internal_reference_number');
            $table->string('dispute_resolution')->nullable()->after('vendor_contract_id');
            $table->string('confidentiality_term')->nullable()->after('dispute_resolution');
            $table->string('parent_agreement')->nullable()->after('confidentiality_term');
            $table->string('replaces_agreement')->nullable()->after('parent_agreement');
            $table->text('notes')->nullable()->after('replaces_agreement');
            $table->foreignIdFor(User::class, 'created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agreements', function ($table) {
            $table->dropForeign(['agreement_owner_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'agreement_owner_id',
                'asset_types_covered',
                'renewal_type',
                'notice_period_days',
                'termination_for_convenience',
                'sub_processing_rights',
                'contract_value',
                'governing_law',
                'liability_cap',
                'insurance_requirements',
                'indemnification',
                'internal_reference_number',
                'vendor_contract_id',
                'dispute_resolution',
                'confidentiality_term',
                'parent_agreement',
                'replaces_agreement',
                'notes',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
