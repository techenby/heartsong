<?php

use App\Enum\Color;
use App\Enum\Day;
use App\Enum\Grade;
use App\Enum\Period;
use App\Livewire\Pages\Courses\Index;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->courseA = Course::factory()
        ->has(
            CourseMeeting::factory()
                ->count(2)
                ->sequence(
                    ['day' => Day::MONDAY, 'period' => Period::FIRST],
                    ['day' => Day::THURSDAY, 'period' => Period::FIRST],
                ), 'meetings')
        ->grade(Grade::FIRST)
        ->color(Color::RED)
        ->create(['homeroom' => 123]);

    $this->courseB = Course::factory()
        ->has(
            CourseMeeting::factory()
                ->count(2)
                ->sequence(
                    ['day' => Day::TUESDAY, 'period' => Period::SECOND],
                    ['day' => Day::TUESDAY, 'period' => Period::THIRD],
                ), 'meetings')
        ->grade(Grade::SECOND)
        ->color(Color::BLACK)
        ->create(['homeroom' => 456]);
});

test('page renders successfully', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('courses'))
        ->assertSeeLivewire(Index::class)
        ->assertStatus(200)
        // calendar view
        ->assertSeeInOrder(['bg-black', '!text-white', '2nd - 456'])
        ->assertSeeInOrder(['bg-red-400', '!text-black', '1st - 123'])
        // table view
        ->assertSeeInOrder(['1st Grade', '123', '1st Period, Monday & Thursday'])
        ->assertSeeInOrder(['2nd Grade', '456', 'Tuesday, 2nd Period & 3rd Period']);
});

test('component renders successfully', function () {
    Livewire::test(Index::class)
        ->assertStatus(200)
        // calendar view
        ->assertSeeInOrder(['bg-red-400', '!text-black', '1st - 123'])
        ->assertSeeInOrder(['bg-black', '!text-white', '2nd - 456'])
        // table view
        ->assertSeeInOrder(['1st Grade', '123', '1st Period, Monday & Thursday'])
        ->assertSeeInOrder(['2nd Grade', '456', 'Tuesday, 2nd Period & 3rd Period']);
});

test('can delete course', function () {
    [$meetingA, $meetingB] = $this->courseA->meetings;

    Livewire::test(Index::class)
        ->call('delete', $this->courseA->id)
        ->assertDontSee('1st Grade')
        ->assertSee('2nd Grade');

    expect($this->courseA->fresh())->toBeNull()
        ->and($meetingA->fresh())->toBeNull()
        ->and($meetingB->fresh())->toBeNull();
});

describe('browser tests', function () {
    test('can delete course', function () {
        $this->actingAs(User::factory()->create());

        visit(route('courses', ['tab' => 'list']))
            ->click("#course-{$this->courseA->id}-actions")
            ->click("#course-{$this->courseA->id}-delete")
            ->assertDontSee('1st Grade');
    });
})->group('browser');
