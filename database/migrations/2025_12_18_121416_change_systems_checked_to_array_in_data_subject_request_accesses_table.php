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
            $table->dropColumn('systems_checked');
            $table->json('systems_checked')->nullable()->after('processing_activity_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_subject_request_accesses', function (Blueprint $table) {
            $table->dropColumn('systems_checked');
            $table->text('systems_checked')->nullable()->after('processing_activity_ids');
        });
    }
};
