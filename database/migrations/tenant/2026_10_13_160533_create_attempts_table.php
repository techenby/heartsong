<?php

use App\Models\Song;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Song::class)->constrained()->cascadeOnDelete();
            $table->date('attempted_on');
            $table->boolean('passed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
