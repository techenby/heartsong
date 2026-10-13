<?php

use App\Models\Period;

test('formatted times use a 12-hour clock without meridiem', function (string $startsAt, string $endsAt, string $formatted) {
    expect(Period::make(['starts_at' => $startsAt, 'ends_at' => $endsAt])->formattedTimes())->toBe($formatted);
})->with([
    ['08:20', '09:20', '8:20-9:20'],
    ['11:20', '12:10', '11:20-12:10'],
    ['13:15', '15:15', '1:15-3:15'],
]);

test('the default bell schedule is seeded in order', function () {
    expect(Period::query()->ordered()->pluck('name')->all())->toBe([
        '1st Period', '2nd Period', '3rd Period', '4th Period', '5th Period', '6th Period', '7th Period',
    ]);
});
