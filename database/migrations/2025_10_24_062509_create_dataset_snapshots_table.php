<?php

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
        Schema::create('dataset_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dataset::class)->constrained('datasets')->onDelete('cascade');
            $table->string('version_tag');
            $table->timestamp('time_range_start')->nullable();
            $table->timestamp('time_range_end')->nullable();
            $table->bigInteger('row_count')->nullable();
            $table->text('quality_checksums')->nullable();
            $table->integer('pii_element_count')->nullable();
            $table->integer('special_category_element_count')->nullable();
            $table->string('masking_anonymization_method')->nullable();
            $table->string('privacy_transform_evidence_ref')->nullable();
            $table->string('residency_zone');
            $table->text('storage_uri');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_snapshots');
    }
};
