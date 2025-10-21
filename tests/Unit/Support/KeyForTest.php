<?php

declare(strict_types=1);

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('returns course-{id} for a model without prefix/suffix', function () {
    $course = Course::factory()->make(['id' => 1]);

    expect(keyFor($course))->toBe('course-1');
});

it('returns {prefix}-course-{id} when prefix provided', function () {
    $course = Course::factory()->make(['id' => 1]);

    expect(keyFor($course, prefix: 'past'))->toBe('past-course-1');
});

it('returns course-{id}-{suffix} when suffix provided', function () {
    $course = Course::factory()->make(['id' => 1]);

    expect(keyFor($course, suffix: 'pending'))->toBe('course-1-pending');
});

it('returns {prefix}-course-{id}-{suffix} when both provided', function () {
    $course = Course::factory()->make(['id' => 1]);

    expect(keyFor($course, 'past', 'pending'))->toBe('past-course-1-pending');
});
