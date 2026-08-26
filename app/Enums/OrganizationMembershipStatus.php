<?php

namespace App\Enums;

enum OrganizationMembershipStatus: string
{
    case Pending = 'PENDING';
    case Active = 'ACTIVE';
    case Rejected = 'REJECTED';
    case Suspended = 'SUSPENDED';
    case Left = 'LEFT';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Active => 'Activa',
            self::Rejected => 'Rechazada',
            self::Suspended => 'Suspendida',
            self::Left => 'Finalizada',
        };
    }
}
