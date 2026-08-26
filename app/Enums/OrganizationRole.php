<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Admin = 'ORG_ADMIN';
    case Member = 'MEMBER';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Member => 'Miembro',
        };
    }
}
