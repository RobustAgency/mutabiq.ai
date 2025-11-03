<?php

use App\Models\AiModelVersion;
use App\Models\Organization;
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
        Schema::create('ai_model_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class, 'organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();
            $table->foreignIdFor(AiModelVersion::class, 'ai_model_version_id')
                ->constrained('ai_model_versions')
                ->cascadeOnDelete();
            $table->string('artifact_type');
            $table->string('uri', 1024);
            $table->string('checksum')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_artifacts');
    }
};
