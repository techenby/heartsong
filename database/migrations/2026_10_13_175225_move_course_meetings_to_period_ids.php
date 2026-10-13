<?php

use App\Models\Period;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_meetings', function (Blueprint $table) {
            $table->foreignIdFor(Period::class)->nullable()->after('day')->constrained()->cascadeOnDelete();
        });

        // Meetings stored the period's name (e.g. "1st Period"), which matches the seeded periods.
        DB::table('course_meetings')->update([
            'period_id' => DB::raw('(select periods.id from periods where periods.name = course_meetings.period)'),
        ]);

        DB::table('course_meetings')->whereNull('period_id')->delete();

        Schema::table('course_meetings', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'day', 'period']);
            $table->dropColumn('period');
        });

        Schema::table('course_meetings', function (Blueprint $table) {
            $table->foreignIdFor(Period::class)->nullable(false)->change();
            $table->unique(['course_id', 'day', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_meetings', function (Blueprint $table) {
            $table->string('period')->nullable();
        });

        DB::table('course_meetings')->update([
            'period' => DB::raw('(select periods.name from periods where periods.id = course_meetings.period_id)'),
        ]);

        Schema::table('course_meetings', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'day', 'period_id']);
            $table->dropConstrainedForeignIdFor(Period::class);
            $table->unique(['course_id', 'day', 'period']);
        });
    }
};
