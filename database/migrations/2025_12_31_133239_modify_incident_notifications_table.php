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
        Schema::table('incident_notifications', function (Blueprint $table) {

            $table->dropColumn([
                'approved_by',
                'approval_ref',
            ]);

            $table->renameColumn('notified_at', 'sent_at');

            $table->string('template')->nullable()->after('ai_incident_id');
            $table->string('language')->nullable()->after('template');
            $table->string('regulatory_basis')->nullable()->after('channel');
            $table->timestamp('notification_deadline')->nullable()->after('regulatory_basis');
            $table->string('sent_by')->nullable()->after('sent_at');
            $table->string('delivery_status')->nullable()->after('sent_by');
            $table->text('response_summary')->nullable()->after('delivery_status');
            $table->timestamp('follow_up_date')->nullable()->after('response_summary');
            $table->text('follow_up_notes')->nullable()->after('follow_up_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->dropColumn([
                'template',
                'language',
                'regulatory_basis',
                'notification_deadline',
                'sent_by',
                'delivery_status',
                'response_summary',
                'follow_up_date',
                'follow_up_notes',
            ]);

            $table->renameColumn('sent_at', 'notified_at');

            $table->string('approved_by')->nullable()->after('notice_link');
            $table->string('approval_ref')->nullable()->after('approved_by');
        });
    }
};
