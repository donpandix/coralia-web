<?php

namespace App\Enums;

enum TagStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Inactive => 'Inactiva',
        };
    }
}
