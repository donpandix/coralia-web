<?php

namespace App\Enums;

enum PieceShareType: string
{
    case Organization = 'ORGANIZATION';
    case Voice = 'VOICE';
    case Group = 'GROUP';
    case Member = 'MEMBER';
}
