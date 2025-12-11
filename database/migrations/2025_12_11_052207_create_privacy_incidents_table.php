<?php

use App\Models\User;
use App\Models\Vendor;
use App\Models\Organization;
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
        Schema::create('privacy_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained()->onDelete('cascade');
            $table->string('incident_code')->unique();
            $table->string('incident_title');
            $table->string('incident_type');
            $table->string('risk_level');
            $table->boolean('is_breach')->default(false);
            $table->json('breach_criteria_met')->nullable();
            $table->date('detected_date')->nullable();
            $table->date('occurred_date')->nullable();
            $table->date('notification_deadline')->nullable();
            $table->integer('hours_to_deadline')->nullable();
            $table->boolean('is_deadline_passed')->default(false);
            $table->text('incident_description');
            $table->text('what_happened');
            $table->text('how_discovered');
            $table->text('data_compromised');
            $table->json('data_categories_affected');
            $table->integer('estimated_affected_subjects');
            $table->json('affected_subject_keys')->nullable();
            $table->string('notification_required');
            $table->string('notification_status')->default('pending');
            $table->boolean('authority_notified')->default(false);
            $table->date('authority_notification_date')->nullable();
            $table->string('supervisory_authority')->nullable();
            $table->string('authority_reference_number')->nullable();
            $table->text('authority_response')->nullable();
            $table->boolean('subjects_notified')->default(false);
            $table->date('subject_notification_date')->nullable();
            $table->string('notification_method')->nullable();
            $table->string('notification_template_used')->nullable();
            $table->text('immediate_actions');
            $table->text('mitigation_measures');
            $table->text('preventive_measures');
            $table->text('root_cause_analysis')->nullable();
            $table->string('responsible_party')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->string('status');
            $table->date('resolution_date')->nullable();
            $table->integer('days_to_resolution')->nullable();
            $table->json('processing_activity_ids')->nullable();
            $table->json('affected_systems');
            $table->boolean('third_party_involved')->default(false);
            $table->foreignIdFor(Vendor::class)->nullable()->constrained()->onDelete('set null');
            $table->json('evidence_uris')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class, 'updated_by')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_incidents');
    }
};
