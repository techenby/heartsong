<?php

namespace App\Enum;

enum Day: int
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;

    public function label(): string
    {
        return match ($this) {
            Day::MONDAY => 'Monday',
            Day::TUESDAY => 'Tuesday',
            Day::WEDNESDAY => 'Wednesday',
            Day::THURSDAY => 'Thursday',
            Day::FRIDAY => 'Friday',
        };
    }
}
