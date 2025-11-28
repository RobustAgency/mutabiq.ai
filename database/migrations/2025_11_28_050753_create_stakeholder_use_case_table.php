<?php

use App\Models\UseCase;
use App\Models\Stakeholder;
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
        Schema::create('stakeholder_use_case', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Stakeholder::class)->constrained('stakeholders')->onDelete('cascade');
            $table->foreignIdFor(UseCase::class)->constrained('use_cases')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakeholder_use_case');
    }
};
