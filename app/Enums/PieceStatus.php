<?php

namespace App\Enums;

enum PieceStatus: string
{
    case Active = 'ACTIVE';
    case Archived = 'ARCHIVED';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Archived => 'Archivada',
        };
    }
}
