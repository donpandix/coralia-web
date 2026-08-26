<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Open = 'OPEN';
    case InReview = 'IN_REVIEW';
    case Resolved = 'RESOLVED';
    case Dismissed = 'DISMISSED';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierto',
            self::InReview => 'En revisión',
            self::Resolved => 'Resuelto',
            self::Dismissed => 'Descartado',
        };
    }
}
