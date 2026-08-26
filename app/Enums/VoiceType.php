<?php

namespace App\Enums;

enum VoiceType: string
{
    case General = 'GENERAL';
    case Soprano = 'SOPRANO';
    case Alto = 'ALTO';
    case Tenor = 'TENOR';
    case Bass = 'BASS';

    public function isChoralVoice(): bool
    {
        return $this !== self::General;
    }

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Soprano => 'Soprano',
            self::Alto => 'Alto',
            self::Tenor => 'Tenor',
            self::Bass => 'Bajo',
        };
    }
}
