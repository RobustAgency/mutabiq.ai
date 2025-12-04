<?php

use App\Models\User;
use App\Models\AiModel;
use App\Models\Framework;
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
        Schema::create('regulatory_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Framework::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(AiModel::class)->nullable()->constrained()->nullOnDelete();
            $table->string('authority');
            $table->json('jurisdiction')->nullable();
            $table->string('submission_type');
            $table->text('content_summary')->nullable();
            $table->string('tracking_id');
            $table->string('status');
            $table->timestamp('submitted_at')->nullable();
            $table->json('commitments')->nullable();
            $table->timestamp('renewal_due_at')->nullable();
            $table->json('evidence_bundle_ids')->nullable();
            $table->foreignIdFor(User::class, 'submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('documents_uri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulatory_submissions');
    }
};
