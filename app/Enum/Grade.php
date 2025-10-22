<?php

namespace App\Enum;

enum Grade: string
{
    case KINDER = 'Kindergarten';
    case FIRST = '1st Grade';
    case SECOND = '2nd Grade';
    case THIRD = '3rd Grade';
    case FOURTH = '4th Grade';
    case FIFTH = '5th Grade';
    case SIXTH = '6th Grade';
    case SEVENTH = '7th Grade';
    case EIGHTH = '8th Grade';
    
    public function short(): string
    {
        return $this === self::KINDER ? 'K' : str($this->value)->before(' ');
    }
}
