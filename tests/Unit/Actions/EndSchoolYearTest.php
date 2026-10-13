<?php

use App\Actions\EndSchoolYear;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;

test('every student and attempt is deleted while classes and songs are kept', function () {
    $course = Course::factory()->create();
    $song = Song::factory()->for($course)->create();
    foreach (Student::factory()->for($course)->count(2)->create() as $student) {
        Attempt::factory()->for($student)->for($song)->create();
    }

    $deleted = app(EndSchoolYear::class)->handle();

    expect($deleted)->toBe(['attempts' => 2, 'students' => 2])
        ->and(Student::count())->toBe(0)
        ->and(Attempt::count())->toBe(0)
        ->and($course->fresh())->not->toBeNull()
        ->and($song->fresh())->not->toBeNull();
});
