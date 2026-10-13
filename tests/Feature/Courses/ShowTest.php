<?php

use App\Models\Course;
use App\Models\Student;

test('the roster lists students by last name using preferred names', function () {
    $user = actingAsTenant();
    $course = Course::factory()
        ->has(Student::factory()->state(['first_name' => 'Robert', 'preferred_name' => 'Bobby', 'last_name' => 'Young']))
        ->has(Student::factory()->state(['first_name' => 'Ada', 'last_name' => 'Abbott']))
        ->create();
    tenancy()->end();

    $this->actingAs($user)
        ->get(route('courses.show', $course))
        ->assertOk()
        ->assertSeeInOrder(['Ada', 'Abbott', 'Bobby', 'Young'])
        ->assertDontSee('Robert');
});

test('student names are escaped', function () {
    $user = actingAsTenant();
    $course = Course::factory()
        ->has(Student::factory()->state(['first_name' => '<script>alert(1)</script>']))
        ->create();
    tenancy()->end();

    $this->actingAs($user)
        ->get(route('courses.show', $course))
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
        ->assertDontSee('<script>alert(1)</script>', escape: false);
});

test('another user\'s class returns 404', function () {
    actingAsTenant();
    $course = Course::factory()->create();
    $otherUser = actingAsTenant();
    tenancy()->end();

    $this->actingAs($otherUser)
        ->get(route('courses.show', $course))
        ->assertNotFound();
});
