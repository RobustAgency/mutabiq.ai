<?php

use App\Models\User;
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
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'name',
                'code',
                'description',
            ]);

            $table->string('reference')->after('id');
            $table->text('requirement_text')->nullable()->after('reference');
            $table->string('category')->nullable()->after('requirement_text');
            $table->string('applicability')->nullable()->after('category');
            $table->date('effective_from')->nullable()->after('applicability');
            $table->date('effective_to')->nullable()->after('effective_from');
            $table->unsignedBigInteger('supersedes_req_id')->nullable()->after('effective_to');
            $table->unsignedBigInteger('superseded_by_req_id')->nullable()->after('supersedes_req_id');
            $table->string('priority')->nullable()->after('superseded_by_req_id');
            $table->json('tags')->nullable()->after('priority');

            $table->foreign('supersedes_req_id')->references('id')->on('requirements')->nullOnDelete();
            $table->foreign('superseded_by_req_id')->references('id')->on('requirements')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropForeign(['supersedes_req_id']);
            $table->dropForeign(['superseded_by_req_id']);
            $table->dropColumn([
                'reference',
                'requirement_text',
                'category',
                'applicability',
                'effective_from',
                'effective_to',
                'supersedes_req_id',
                'superseded_by_req_id',
                'priority',
                'tags',
            ]);
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
        });
    }
};
