<?php

use App\Enums\Period;

test('formatted times use a 12-hour clock without meridiem', function (Period $period, string $formatted) {
    expect($period->formattedTimes())->toBe($formatted);
})->with([
    [Period::First, '8:20-9:20'],
    [Period::Fourth, '11:20-12:10'],
    [Period::Sixth, '1:15-3:15'],
]);
