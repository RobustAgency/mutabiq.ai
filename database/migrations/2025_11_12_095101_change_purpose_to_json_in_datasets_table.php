<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing string values to JSON format
        DB::table('datasets')->get()->each(function ($row) {
            $value = $row->purpose;
            if (!is_null($value) && !json_decode($value)) {
                DB::table('datasets')
                    ->where('id', $row->id)
                    ->update(['purpose' => json_encode($value)]);
            }
        });

        Schema::table('datasets', function (Blueprint $table) {
            $table->json('purpose')->change();
        });
    }

    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->string('purpose')->change();
        });
    }
};
