<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use Livewire\Livewire;

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

test('a student\'s preferred name and pronouns can be edited', function () {
    actingAsTenant();
    $student = Student::factory()->create(['first_name' => 'Robert']);

    Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('editStudent', $student->id)
        ->set('preferredName', ' Bobby ')
        ->set('pronouns', 'he/him')
        ->call('saveStudent')
        ->assertHasNoErrors()
        ->assertSee('Bobby');

    expect($student->fresh())
        ->preferred_name->toBe('Bobby')
        ->pronouns->toBe('he/him');
});

test('clearing a student\'s details stores nothing', function () {
    actingAsTenant();
    $student = Student::factory()->create(['preferred_name' => 'Bobby', 'pronouns' => 'he/him']);

    Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('editStudent', $student->id)
        ->set('preferredName', '  ')
        ->set('pronouns', '')
        ->call('saveStudent');

    expect($student->fresh())
        ->preferred_name->toBeNull()
        ->pronouns->toBeNull();
});

test('student details have length limits', function () {
    actingAsTenant();
    $student = Student::factory()->create();

    Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('editStudent', $student->id)
        ->set('preferredName', str_repeat('a', 101))
        ->set('pronouns', str_repeat('a', 51))
        ->call('saveStudent')
        ->assertHasErrors(['preferredName' => 'max', 'pronouns' => 'max']);
});

test('students from another class cannot be edited', function () {
    actingAsTenant();
    $course = Course::factory()->create();
    $otherStudent = Student::factory()->create();

    Livewire::test('pages::courses.show', ['course' => $course])
        ->call('editStudent', $otherStudent->id)
        ->assertStatus(404);
});

test('a student can be marked as having left and restored', function () {
    actingAsTenant();
    $student = Student::factory()->create(['first_name' => 'Robert', 'preferred_name' => 'Bobby', 'last_name' => 'Cruz']);

    $component = Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('withdrawStudent', $student->id)
        ->assertSee(['Left the class', 'Bobby Cruz'])
        ->assertDontSee('Robert');

    expect($student->fresh()->trashed())->toBeTrue();

    $component->call('restoreStudent', $student->id)
        ->assertDontSee('Left the class');

    expect($student->fresh()->trashed())->toBeFalse();
});

test('a student\'s testing history is shown and mistakes can be deleted', function () {
    actingAsTenant();
    $student = Student::factory()->create();
    $song = Song::factory()->for($student->course)->create(['name' => 'Hot Cross Buns']);
    $attempt = Attempt::factory()->for($student)->for($song)->create(['notes' => 'Wrong student']);
    $otherAttempt = Attempt::factory()->create();

    Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('showHistory', $student->id)
        ->assertSee(['Hot Cross Buns', 'Wrong student'])
        ->call('deleteAttempt', $otherAttempt->id)
        ->assertStatus(404);

    Livewire::test('pages::courses.show', ['course' => $student->course])
        ->call('showHistory', $student->id)
        ->call('deleteAttempt', $attempt->id)
        ->assertDontSee('Wrong student');

    expect(Attempt::count())->toBe(1);
});
