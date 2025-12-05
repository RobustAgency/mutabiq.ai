<?php

use App\Models\Organization;
use App\Models\AiRiskRegister;
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
        Schema::create('kri_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Organization::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(AiRiskRegister::class)->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('definition');
            $table->string('directionality');
            $table->string('unit')->nullable();
            $table->string('sample_window');
            $table->decimal('threshold_warning', 10, 2);
            $table->decimal('threshold_critical', 10, 2);
            $table->string('data_source');
            $table->string('collection_method');
            $table->string('frequency');
            $table->json('alert_routing');
            $table->string('action_on_breach');
            $table->string('status');
            $table->string('owner_team');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kri_indicators');
    }
};
