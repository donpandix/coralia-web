<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Admin = 'ORG_ADMIN';
    case Member = 'MEMBER';
}
