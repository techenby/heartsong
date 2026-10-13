<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SQLite doesn't index foreign keys automatically; these back the lookups and cascades the screens rely on.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', fn (Blueprint $table) => $table->index('course_id'));
        Schema::table('songs', fn (Blueprint $table) => $table->index('course_id'));
        Schema::table('attempts', fn (Blueprint $table) => $table->index('song_id'));
        Schema::table('course_meetings', fn (Blueprint $table) => $table->index('period_id'));
    }

    public function down(): void
    {
        Schema::table('students', fn (Blueprint $table) => $table->dropIndex(['course_id']));
        Schema::table('songs', fn (Blueprint $table) => $table->dropIndex(['course_id']));
        Schema::table('attempts', fn (Blueprint $table) => $table->dropIndex(['song_id']));
        Schema::table('course_meetings', fn (Blueprint $table) => $table->dropIndex(['period_id']));
    }
};
