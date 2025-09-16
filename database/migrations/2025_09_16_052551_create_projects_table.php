<?php

use App\Enums\UserRole;
use App\Models\Framework;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Project;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('progress')->default(0);
            $table->string('governance_pilar');
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('framework_project', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Framework::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Project::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('framework_project');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
