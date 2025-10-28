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
        Schema::create('data_elements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('business_definition')->nullable();
            $table->string('data_type');
            $table->string('format')->nullable();
            $table->string('sensitivity');
            $table->string('pii_flag');
            $table->string('personal_data_category')->nullable();
            $table->string('special_category_flag');
            $table->string('cde_flag');
            $table->string('cde_category')->nullable();
            $table->string('owner_team')->nullable();
            $table->text('quality_rules_ref')->nullable();
            $table->string('catalog_column_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_elements');
    }
};
