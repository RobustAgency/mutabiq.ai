<?php

use App\Models\User;
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
        Schema::create('data_subject_request_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('request_code');
            $table->string('request_type');
            $table->string('subject_identifier');
            $table->string('subject_key')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('subject_realm');
            $table->string('verification_status')->default('pending');
            $table->string('verification_method')->nullable();
            $table->date('verification_date')->nullable();
            $table->foreignIdFor(User::class, 'verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('request_details');
            $table->json('requested_data_categories')->nullable();
            $table->string('request_source');
            $table->date('submitted_date');
            $table->date('due_date');
            $table->date('extended_due_date');
            $table->date('response_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status');
            $table->string('priority')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->foreignIdFor(User::class, 'assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assigned_date')->nullable();
            $table->string('response_method');
            $table->string('response_format')->nullable();
            $table->string('response_uri')->nullable();
            $table->text('response_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('jurisdiction');
            $table->json('processing_activity_ids')->nullable();
            $table->text('systems_checked')->nullable();
            $table->text('records_found')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_subject_request_accesses');
    }
};
