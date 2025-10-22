<?php

declare(strict_types=1);

use App\Enum\Period;

it('gets correct times for each period', function (Period $enum, array $result) {
    expect($enum->times())->toBe($result);
})->with([
    [Period::FIRST, ['start' => '08:20:00', 'end' => '09:20:00']],
    [Period::SECOND, ['start' => '09:20:00', 'end' => '10:20:00']],
    [Period::THIRD, ['start' => '10:20:00', 'end' => '11:20:00']],
    [Period::FOURTH, ['start' => '11:20:00', 'end' => '12:10:00']],
    [Period::FIFTH, ['start' => '12:10:00', 'end' => '13:10:00']],
    [Period::SIXTH, ['start' => '13:15:00', 'end' => '15:15:00']],
    [Period::SEVENTH, ['start' => '15:15:00', 'end' => '16:15:00']],
]);
