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
        Schema::table('controls', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'question',
                'summary',
                'description',
            ]);
            $table->string('reference')->nullable()->unique()->after('name');
            $table->text('objective')->nullable()->after('reference');
            $table->string('testing_method')->nullable()->after('objective');
            $table->string('testing_frequency')->nullable()->after('testing_method');
            $table->text('evidence_expectations')->nullable()->after('testing_frequency');
            $table->text('applicability_criteria')->nullable()->after('evidence_expectations');
            $table->string('status')->nullable()->after('applicability_criteria');
            $table->date('last_test_date')->nullable()->after('status');
            $table->date('next_test_due')->nullable()->after('last_test_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('controls', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id')->unique();
            $table->text('question')->nullable()->after('code');
            $table->text('summary')->nullable()->after('question');
            $table->text('description')->nullable()->after('summary');
            $table->dropUnique(['reference']);
            $table->dropColumn([
                'reference',
                'objective',
                'testing_method',
                'testing_frequency',
                'evidence_expectations',
                'applicability_criteria',
                'status',
                'last_test_date',
                'next_test_due',
            ]);
        });
    }
};
