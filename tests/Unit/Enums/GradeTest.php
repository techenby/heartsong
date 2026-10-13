<?php

use App\Enums\Grade;

test('short labels', function (Grade $grade, string $short) {
    expect($grade->short())->toBe($short);
})->with([
    [Grade::Kindergarten, 'K'],
    [Grade::First, '1st'],
    [Grade::Eighth, '8th'],
]);
