<?php

use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Student;

test('meets lists the days when every meeting shares a period', function () {
    $course = Course::factory()->create();
    $course->meetings()->createMany([
        ['day' => Day::Thursday, 'period' => Period::First],
        ['day' => Day::Monday, 'period' => Period::First],
    ]);

    expect($course->meets)->toBe('1st Period, Monday & Thursday');
});

test('meets lists the periods when every meeting shares a day', function () {
    $course = Course::factory()->create();
    $course->meetings()->createMany([
        ['day' => Day::Friday, 'period' => Period::Third],
        ['day' => Day::Friday, 'period' => Period::First],
    ]);

    expect($course->meets)->toBe('Friday, 1st Period & 3rd Period');
});

test('meets lists each meeting in weekly order when days and periods vary', function () {
    $course = Course::factory()->create();
    $course->meetings()->createMany([
        ['day' => Day::Tuesday, 'period' => Period::Second],
        ['day' => Day::Monday, 'period' => Period::Fifth],
    ]);

    expect($course->meets)->toBe('5th Period Monday, 2nd Period Tuesday');
});

test('meets is empty for a course with no meetings', function () {
    expect(Course::factory()->create()->meets)->toBe('');
});

test('formatted combines the short grade and homeroom', function () {
    $course = Course::factory()->create(['grade' => Grade::Kindergarten, 'homeroom' => 'A101']);

    expect($course->formatted)->toBe('K - A101');
});

test('deleting a course deletes its meetings and students', function () {
    $course = Course::factory()
        ->has(CourseMeeting::factory(), 'meetings')
        ->has(Student::factory()->count(2))
        ->create();

    $course->delete();

    expect(CourseMeeting::count())->toBe(0)
        ->and(Student::count())->toBe(0);
});
