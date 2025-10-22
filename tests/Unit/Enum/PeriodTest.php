<?php

declare(strict_types=1);

use App\Enum\Period;

it('gets correct times for each period', function (Period $enum, array $result) {
    expect($enum->times())->toBe($result);
})->with([
    [Period::FIRST, ['start' => '08:30:00', 'end' => '09:30:00']],
    [Period::SECOND, ['start' => '09:35:00', 'end' => '10:35:00']],
    [Period::THIRD, ['start' => '10:40:00', 'end' => '11:40:00']],
    [Period::FOURTH, ['start' => '11:40:00', 'end' => '12:25:00']],
    [Period::FIFTH, ['start' => '12:25:00', 'end' => '13:25:00']],
    [Period::SIXTH, ['start' => '13:30:00', 'end' => '15:30:00']],
    [Period::SEVENTH, ['start' => '15:35:00', 'end' => '16:15:00']],
]);
