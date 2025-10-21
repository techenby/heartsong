<?php

namespace App\Enum;

enum Period: string
{
    case FIRST = '1st Period';
    case SECOND = '2nd Period';
    case THIRD = '3rd Period';
    case FOURTH = '4th Period';
    case FIFTH = '5th Period';
    case SIXTH = '6th Period';
    case SEVENTH = '7th Period';

    public function times(): array
    {
        return match($this) {
            self::FIRST => ['start' => '08:30:00', 'end' => '09:30:00'],
            self::SECOND => ['start' => '09:35:00', 'end' => '10:35:00'],
            self::THIRD => ['start' => '10:40:00', 'end' => '11:40:00'],
            self::FOURTH => ['start' => '11:40:00', 'end' => '12:25:00'],
            self::FIFTH => ['start' => '12:25:00', 'end' => '13:25:00'],
            self::SIXTH => ['start' => '13:30:00', 'end' => '15:30:00'],
            self::SEVENTH => ['start' => '15:35:00', 'end' => '16:15:00'],
        };
    }
}
