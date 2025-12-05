<?php

use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
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
        Schema::create('compliance_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Requirement::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(AiModel::class)->nullable()->constrained()->nullOnDelete();
            $table->string('artifact_type');
            $table->string('artifact_uri');
            $table->json('sample_ids')->nullable();
            $table->string('sampling_method')->nullable();
            $table->timestamp('collection_period_start')->nullable();
            $table->timestamp('collection_period_end')->nullable();
            $table->foreignIdFor(User::class, 'collected_by')->nullable()->constrained()->nullOnDelete();
            $table->string('review_outcome')->nullable();
            $table->foreignIdFor(User::class, 'reviewed_by')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('hash_checksum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_evidences');
    }
};
