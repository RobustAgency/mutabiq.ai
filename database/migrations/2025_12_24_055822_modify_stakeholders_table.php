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
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_id',
                'legal_name',
                'active',
            ]);

            $table->string('first_name')->nullable()->after('type');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('mobile')->nullable()->after('phone');
            $table->string('employee_id')->nullable()->after('external_ref');
            $table->string('cost_center')->nullable()->after('employee_id');
            $table->string('manager')->nullable()->after('cost_center');
            $table->string('delegate')->nullable()->after('manager');
            $table->string('status')->default('active')->after('delegate');
            $table->text('notes')->nullable()->after('status');
            $table->date('start_date')->nullable()->after('notes');
            $table->date('end_date')->nullable()->after('start_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'secondary_email',
                'mobile',
                'employee_id',
                'cost_center',
                'manager',
                'delegate',
                'status',
                'notes',
                'start_date',
                'end_date',
            ]);

            $table->unsignedBigInteger('vendor_id')->nullable()->after('phone');
            $table->string('legal_name')->nullable()->after('display_name');
            $table->boolean('active')->default(true)->after('external_ref');
        });
    }
};
