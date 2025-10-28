<?php

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
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vendor::class)->constrained('vendors')->onDelete('restrict');
            $table->string('agreement_type');
            $table->string('status');
            $table->dateTime('effective_from');
            $table->dateTime('effective_to');
            $table->string('training_opt_out')->nullable();
            $table->string('audit_rights')->nullable();
            $table->string('transfer_mechanism')->nullable();
            $table->json('sla_terms')->nullable();
            $table->string('doc_ref');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
