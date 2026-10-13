<?php

use App\Enums\Grade;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Student;
use Database\Seeders\DevelopmentSeeder;

test('one class per grade meets twice without double-booking a time slot', function () {
    $this->seed(DevelopmentSeeder::class);

    expect(Course::count())->toBe(count(Grade::cases()))
        ->and(Course::pluck('grade')->all())->toBe(Grade::cases())
        ->and(CourseMeeting::count())->toBe(count(Grade::cases()) * 2)
        ->and(CourseMeeting::query()->distinct()->count(DB::raw("day || '-' || period_id")))->toBe(CourseMeeting::count());
});

test('rosters are Care Bears, with the villains in seventh grade', function () {
    $this->seed(DevelopmentSeeder::class);

    $seventh = Course::firstWhere('grade', Grade::Seventh);
    $villains = collect(DevelopmentSeeder::VILLAINS)->map(fn (array $name) => implode(' ', $name));

    expect($seventh->students->map(fn (Student $student) => "{$student->first_name} {$student->last_name}")->all())
        ->toBe($villains->all())
        ->and(Course::where('grade', '!=', Grade::Seventh)->get()->every(fn (Course $course) => $course->students->isNotEmpty()))->toBeTrue()
        ->and(Student::whereIn('last_name', ['Coldheart', 'Freeze'])->whereHas('course', fn ($query) => $query->where('grade', '!=', Grade::Seventh))->exists())->toBeFalse();
});

test('running it again replaces the demo data instead of duplicating it', function () {
    $this->seed(DevelopmentSeeder::class);
    $this->seed(DevelopmentSeeder::class);

    expect(Course::count())->toBe(count(Grade::cases()))
        ->and(Student::count())->toBe(count(DevelopmentSeeder::CARE_BEARS) + count(DevelopmentSeeder::VILLAINS));
});

test('each class gets its grade\'s songs in order', function () {
    $this->seed(DevelopmentSeeder::class);

    Course::all()->each(fn (Course $course) => expect($course->songs()->pluck('name')->all())
        ->toBe(DevelopmentSeeder::songsFor($course->grade))
        ->not->toBeEmpty());
});
