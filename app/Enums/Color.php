<?php

namespace App\Enums;

enum Color: string
{
    case Red = 'Red';
    case Orange = 'Orange';
    case Yellow = 'Yellow';
    case Green = 'Green';
    case Blue = 'Blue';
    case Purple = 'Purple';
    case White = 'White';
    case Gray = 'Gray';
    case Black = 'Black';

    public function background(): string
    {
        return match ($this) {
            self::Red => 'bg-red-400',
            self::Orange => 'bg-orange-400',
            self::Yellow => 'bg-yellow-400',
            self::Green => 'bg-green-400',
            self::Blue => 'bg-blue-400',
            self::Purple => 'bg-purple-400',
            self::White => 'bg-white',
            self::Gray => 'bg-gray-400',
            self::Black => 'bg-black',
        };
    }

    public function text(): string
    {
        return $this === self::Black ? 'text-white' : 'text-black';
    }
}
