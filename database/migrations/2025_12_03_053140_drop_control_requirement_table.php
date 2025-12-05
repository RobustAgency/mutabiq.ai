<?php

use App\Models\Control;
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
        Schema::dropIfExists('control_requirement');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('control_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Control::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Requirement::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
