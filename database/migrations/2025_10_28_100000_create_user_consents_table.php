<?php

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
        Schema::create('user_consents', function (Blueprint $table) {
            $table->id();
            $table->string('subject_key');
            $table->string('subject_realm');
            $table->string('jurisdiction');
            $table->json('consent_purpose');
            $table->string('consent_status');
            $table->string('legal_basis');
            $table->string('source_system');
            $table->string('evidence_ref');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('scope')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_consents');
    }
};
