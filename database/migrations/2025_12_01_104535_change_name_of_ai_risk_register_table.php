<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('ai_risk_register', 'ai_risk_registers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('ai_risk_registers', 'ai_risk_register');
    }
};
