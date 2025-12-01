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
        Schema::table('ai_model_artifacts', function (Blueprint $table) {
            // Example modification: adding a new column
            $table->string('environment')->nullable()->after('notes');
            $table->string('file_format')->nullable()->after('artifact_type');
            $table->string('checksum_algorithm')->nullable()->after('checksum');
            $table->string('checksum_value')->nullable()->after('checksum_algorithm');
            $table->string('file')->nullable()->after('uri');

            $table->dropColumn('checksum');
            $table->dropColumn('created_by');

            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->string('uri')->nullable()->change(); // Make uri nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_model_artifacts', function (Blueprint $table) {
            $table->dropColumn('environment');
            $table->dropColumn('file_format');
            $table->dropColumn('checksum_algorithm');
            $table->dropColumn('checksum_value');
            $table->dropColumn('file');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');

            $table->string('created_by')->nullable()->after('notes');
            $table->string('checksum')->nullable()->after('uri');
        });
    }
};
