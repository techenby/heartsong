<?php

namespace App\Enum;

enum Color: string
{
    case RED = 'Red';
    case ORANGE = 'Orange';
    case YELLOW = 'Yellow';
    case GREEN = 'Green';
    case BLUE = 'Blue';
    case PURPLE = 'Purple';
    case WHITE = 'White';
    case GRAY = 'Gray';
    case BLACK = 'Black';

    public function background(): string
    {
        return match ($this) {
            self::RED => 'bg-red-400',
            self::ORANGE => 'bg-orange-400',
            self::YELLOW => 'bg-yellow-400',
            self::GREEN => 'bg-green-400',
            self::BLUE => 'bg-blue-400',
            self::PURPLE => 'bg-purple-400',
            self::WHITE => 'bg-white',
            self::GRAY => 'bg-gray-400',
            self::BLACK => 'bg-black',
        };
    }

    public function text(): string
    {
        return match ($this) {
            self::RED => '!text-black', self::ORANGE, self::YELLOW, self::GREEN, self::BLUE, self::PURPLE, self::WHITE, self::GRAY => '!text-black',
            self::BLACK => '!text-white',
        };
    }
}
