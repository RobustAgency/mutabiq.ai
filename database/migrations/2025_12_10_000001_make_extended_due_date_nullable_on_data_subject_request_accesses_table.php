<?php

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
        Schema::table('data_subject_request_accesses', function (Blueprint $table) {
            $table->date('extended_due_date')->nullable()->change();
            $table->string('response_method')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_subject_request_accesses', function (Blueprint $table) {
            $table->date('extended_due_date')->nullable(false)->change();
            $table->string('response_method')->nullable(false)->change();
        });
    }
};
