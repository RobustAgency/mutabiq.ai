<?php

use App\Models\Tag;
use App\Models\User;
use App\Models\Control;
use App\Models\Framework;
use App\Models\Requirement;
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
        Schema::create('controls', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('question')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::create('control_framework', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Framework::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['control_id', 'framework_id']);
        });

        Schema::create('control_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Requirement::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['control_id', 'requirement_id']);
        });

        Schema::create('control_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['control_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_tag');
        Schema::dropIfExists('control_requirement');
        Schema::dropIfExists('control_framework');
        Schema::dropIfExists('controls');
    }
};
