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
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['stakeholder_id']);
            $table->dropColumn('stakeholder_id');

            $table->json('type')->nullable()->after('status');
            $table->string('data_processing_role')->nullable()->after('type');
            $table->string('service_provided')->nullable()->after('data_processing_role');
            $table->string('duns_number')->nullable()->after('metadata');
            $table->string('lei_number')->nullable()->after('duns_number');
            $table->string('tax_id')->nullable()->after('lei_number');
            $table->string('stock_ticker')->nullable()->after('tax_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'data_processing_role',
                'service_provided',
                'duns_number',
                'lei_number',
                'tax_id',
                'stock_ticker',
            ]);

            $table->foreignId('stakeholder_id')->nullable()->after('status')->constrained('stakeholders')->nullOnDelete();
        });
    }
};
