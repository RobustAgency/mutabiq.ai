<?php

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
        Schema::create('pdp_processing_registers', function (Blueprint $table) {
            $table->id();
            $table->text('purpose');
            $table->string('controller_role');
            $table->json('data_subject_categories');
            $table->json('personal_data_categories');
            $table->string('lawful_basis');
            $table->text('lawful_basis_detail')->nullable();
            $table->string('retention_policy_ref')->nullable();
            $table->json('recipients')->nullable();
            $table->string('international_transfer_ref')->nullable();
            $table->string('dpia_required_flag')->nullable();
            $table->text('security_measures_ref')->nullable();
            $table->string('owner_team');
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_to')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdp_processing_registers');
    }
};
