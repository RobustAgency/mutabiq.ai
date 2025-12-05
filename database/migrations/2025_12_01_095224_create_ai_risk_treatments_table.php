<?php

use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\AiRiskRegister;
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
        Schema::create('ai_risk_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained('organizations')->onDelete('cascade');
            $table->foreignIdFor(AiRiskRegister::class)->constrained('ai_risk_register')->onDelete('cascade');
            $table->string('treatment_type');
            $table->text('plan_summary');
            $table->foreignIdFor(Stakeholder::class, 'owner_stakeholder_id')->nullable()->constrained('stakeholders')->nullOnDelete();
            $table->json('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status');
            $table->string('expected_residual_level')->nullable();
            $table->string('result_verification')->nullable();
            $table->string('evidence_link')->nullable();
            $table->string('linked_capa_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_risk_treatments');
    }
};
