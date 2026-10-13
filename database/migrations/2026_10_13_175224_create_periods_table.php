<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('starts_at', 5);
            $table->string('ends_at', 5);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // A starting schedule the teacher edits in Settings. There's no db:seed on device, so it's seeded here.
        $defaults = [
            ['1st Period', '08:20', '09:20'],
            ['2nd Period', '09:20', '10:20'],
            ['3rd Period', '10:20', '11:20'],
            ['4th Period', '11:20', '12:10'],
            ['5th Period', '12:10', '13:10'],
            ['6th Period', '13:15', '15:15'],
            ['7th Period', '15:15', '16:15'],
        ];

        DB::table('periods')->insert(array_map(fn (array $period, int $position) => [
            'name' => $period[0],
            'starts_at' => $period[1],
            'ends_at' => $period[2],
            'position' => $position,
            'created_at' => now(),
            'updated_at' => now(),
        ], $defaults, array_keys($defaults)));
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
