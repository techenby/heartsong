<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained('courses');
            $table->tinyInteger('day');
            $table->time('start');
            $table->time('end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_meetings');
    }
};
