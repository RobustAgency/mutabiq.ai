<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->string('consent_code');
            $table->string('subject_key');
            $table->string('subject_realm');
            $table->string('subject_age_group')->nullable();
            $table->string('purpose');
            $table->foreignIdFor(RecordOfProcessingActivity::class)->constrained('record_of_processing_activities')->onDelete('cascade');
            $table->string('status');
            $table->string('lifecycle_stage')->nullable();
            $table->string('consent_version');
            $table->text('consent_text');
            $table->string('consent_method');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->date('obtained_date')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->date('last_refreshed_date')->nullable();
            $table->string('source_system');
            $table->string('evidence_uri')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('language');
            $table->string('jurisdiction');
            $table->json('data_categories');
            $table->boolean('can_withdraw')->default(true);
            $table->string('withdrawal_method');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
