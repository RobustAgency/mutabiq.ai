<?php

use App\Models\Stakeholder;
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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name');
            $table->string('legal_name');
            $table->string('hq_country', 2);
            $table->string('risk_tier');
            $table->string('status');
            $table->foreignIdFor(Stakeholder::class)->constrained('stakeholders')->onDelete('restrict');
            $table->json('primary_contacts')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
