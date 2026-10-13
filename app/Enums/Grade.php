<?php

namespace App\Enums;

use App\Enums\Concerns\HasOrdinal;

enum Grade: string
{
    use HasOrdinal;

    case Kindergarten = 'Kindergarten';
    case First = '1st Grade';
    case Second = '2nd Grade';
    case Third = '3rd Grade';
    case Fourth = '4th Grade';
    case Fifth = '5th Grade';
    case Sixth = '6th Grade';
    case Seventh = '7th Grade';
    case Eighth = '8th Grade';

    public function short(): string
    {
        return $this === self::Kindergarten ? 'K' : str($this->value)->before(' ')->toString();
    }
}
