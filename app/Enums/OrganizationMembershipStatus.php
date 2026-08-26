<?php

namespace App\Enums;

enum OrganizationMembershipStatus: string
{
    case Pending = 'PENDING';
    case Active = 'ACTIVE';
    case Rejected = 'REJECTED';
    case Suspended = 'SUSPENDED';
    case Left = 'LEFT';
}
