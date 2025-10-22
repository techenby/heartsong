<?php

namespace App\Enum;

use Illuminate\Support\Carbon;

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
        return match ($this) {
            self::FIRST => ['start' => '08:20:00', 'end' => '09:20:00'],
            self::SECOND => ['start' => '09:20:00', 'end' => '10:20:00'],
            self::THIRD => ['start' => '10:20:00', 'end' => '11:20:00'],
            self::FOURTH => ['start' => '11:20:00', 'end' => '12:10:00'],
            self::FIFTH => ['start' => '12:10:00', 'end' => '13:10:00'],
            self::SIXTH => ['start' => '13:15:00', 'end' => '15:15:00'],
            self::SEVENTH => ['start' => '15:15:00', 'end' => '16:15:00'],
        };
    }

    public function formattedTimes(): string
    {
        $start = Carbon::parse($this->times()['start'], 'America/Chicago')->format('g:i');
        $end = Carbon::parse($this->times()['end'], 'America/Chicago')->format('g:i');

        return "{$start}-{$end}";
    }
}
