<?php

use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
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
        Schema::create('ai_model_dataset', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiModel::class)->constrained('ai_models')->onDelete('cascade');
            $table->foreignIdFor(AiModelVersion::class)->constrained('ai_model_versions')->onDelete('cascade');
            $table->foreignIdFor(Dataset::class)->nullable()->constrained('datasets')->onDelete('cascade');
            $table->foreignIdFor(DatasetSnapshot::class)->nullable()->constrained('dataset_snapshots')->onDelete('cascade');
            $table->string('role');
            $table->string('access_path')->nullable();
            $table->string('transform_pack_link')->nullable();
            $table->string('license_check_ref')->nullable();
            $table->string('privacy_check_ref')->nullable();
            $table->string('eligibility_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_dataset');
    }
};
