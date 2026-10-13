<?php

namespace App\Enums;

use App\Enums\Concerns\HasOrdinal;

enum Day: string
{
    use HasOrdinal;

    case Monday = 'Monday';
    case Tuesday = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday = 'Thursday';
    case Friday = 'Friday';
}
