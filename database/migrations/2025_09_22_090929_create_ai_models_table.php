<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignIdFor(Organization::class)->constrained()->cascadeOnDelete();
            $table->string('primary_category');
            $table->string('type');
            $table->string('domain_specialization');
            $table->string('operational_status');
            $table->string('business_status');
            $table->unsignedBigInteger('total_versions')->default(1);
            $table->string('strategic_importance');
            $table->string('regulatory_risk_classification');
            $table->string('organizational_role');
            $table->string('ownership_type');
            $table->string('source_organization');
            $table->string('current_owner');
            $table->string('development_source');
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
