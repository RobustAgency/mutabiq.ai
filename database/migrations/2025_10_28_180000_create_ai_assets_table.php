<?php

use App\Models\Agreement;
use App\Models\Vendor;
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
        Schema::create('ai_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendor::class)->nullable()->constrained('vendors')->onDelete('set null');
            $table->dateTime('vendor_effective_from')->nullable();
            $table->dateTime('vendor_effective_to')->nullable();
            $table->foreignIdFor(Agreement::class, 'vendor_agreement_id')->nullable()->constrained('agreements')->onDelete('set null');
            $table->unsignedBigInteger('vendor_assessment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_assets');
    }
};
