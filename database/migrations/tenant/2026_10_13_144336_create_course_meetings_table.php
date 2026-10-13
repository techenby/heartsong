<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->string('day');
            $table->string('period');
            $table->timestamps();

            $table->unique(['course_id', 'day', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_meetings');
    }
};
