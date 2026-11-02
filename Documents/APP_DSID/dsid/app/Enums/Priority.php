<?php

namespace App\Enums;

enum Priority: string
{
    case FAIBLE = 'faible';
    case MOYENNE = 'moyenne';
    case HAUTE = 'haute';

    public function label(): string
    {
        return match ($this) {
            self::FAIBLE => 'Faible',
            self::MOYENNE => 'Moyenne',
            self::HAUTE => 'Haute',
        };
    }
}
