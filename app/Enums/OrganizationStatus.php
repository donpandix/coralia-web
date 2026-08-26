<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case Active = 'ACTIVE';
    case Suspended = 'SUSPENDED';
    case Archived = 'ARCHIVED';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Suspended => 'Suspendida',
            self::Archived => 'Archivada',
        };
    }
}
