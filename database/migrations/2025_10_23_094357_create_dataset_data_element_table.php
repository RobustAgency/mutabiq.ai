<?php

use App\Models\DataElement;
use App\Models\Dataset;
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
        Schema::create('dataset_element', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dataset::class)->constrained('datasets')->onDelete('cascade');
            $table->foreignIdFor(DataElement::class)->constrained('data_elements')->onDelete('cascade');
            $table->string('column_name');
            $table->string('nullable');
            $table->string('sensitivity_override')->nullable();
            $table->string('pii_override')->default('Inherit');
            $table->string('transform_applied')->nullable();
            $table->text('quality_rules_applied')->nullable();
            $table->string('cde_in_dataset');
            $table->string('cde_category_in_dataset')->nullable();
            $table->string('lineage_source_column')->nullable();
            $table->string('deprecated')->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_element');
    }
};
