<?php

declare(strict_types=1);

use App\Enum\Day;
use App\Enum\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('formats meetings with same period across multiple days', function (): void {
    $course = Course::factory()->create();

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::MONDAY,
        'period' => Period::FIRST,
    ]);

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::THURSDAY,
        'period' => Period::FIRST,
    ]);

    expect($course->fresh()->meets)->toBe('1st Period, Monday & Thursday');
});

it('formats meetings with same day across multiple periods', function (): void {
    $course = Course::factory()->create();

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::MONDAY,
        'period' => Period::FIRST,
    ]);

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::MONDAY,
        'period' => Period::SECOND,
    ]);

    expect($course->fresh()->meets)->toBe('Monday, 1st Period & 2nd Period');
});

it('formats mixed meetings as period then day list', function (): void {
    $course = Course::factory()->create();

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::MONDAY,
        'period' => Period::FIRST,
    ]);

    CourseMeeting::factory()->create([
        'course_id' => $course->id,
        'day' => Day::THURSDAY,
        'period' => Period::THIRD,
    ]);

    expect($course->fresh()->meets)->toBe('1st Period Monday, 3rd Period Thursday');
});
