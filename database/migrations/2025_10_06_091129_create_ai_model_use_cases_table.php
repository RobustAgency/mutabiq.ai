<?php

use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\UseCase;
use App\Models\User;
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
        Schema::create('ai_model_use_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiModel::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(UseCase::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(AiModelVersion::class)->nullable()->constrained()->onDelete('set null');
            $table->string('relationship_type');
            $table->foreignIdFor(User::class, 'created_by');
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_use_cases');
    }
};
