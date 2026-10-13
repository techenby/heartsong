<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum Period: string
{
    case First = '1st Period';
    case Second = '2nd Period';
    case Third = '3rd Period';
    case Fourth = '4th Period';
    case Fifth = '5th Period';
    case Sixth = '6th Period';
    case Seventh = '7th Period';

    /**
     * @return array{start: string, end: string}
     */
    public function times(): array
    {
        return match ($this) {
            self::First => ['start' => '08:20', 'end' => '09:20'],
            self::Second => ['start' => '09:20', 'end' => '10:20'],
            self::Third => ['start' => '10:20', 'end' => '11:20'],
            self::Fourth => ['start' => '11:20', 'end' => '12:10'],
            self::Fifth => ['start' => '12:10', 'end' => '13:10'],
            self::Sixth => ['start' => '13:15', 'end' => '15:15'],
            self::Seventh => ['start' => '15:15', 'end' => '16:15'],
        };
    }

    public function formattedTimes(): string
    {
        ['start' => $start, 'end' => $end] = $this->times();

        return Carbon::parse($start)->format('g:i').'-'.Carbon::parse($end)->format('g:i');
    }
}
