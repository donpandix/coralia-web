<?php

namespace App\Enums;

enum ReportTargetType: string
{
    case User = 'USER';
    case Piece = 'PIECE';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Piece => 'Pieza',
        };
    }
}
