<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Framework;
use App\Models\Project;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('framework_project');

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignIdFor(Framework::class)->nullable()->constrained()->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['framework_id']);
            $table->dropColumn('framework_id');
        });

        Schema::create('framework_project', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Framework::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Project::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
};
