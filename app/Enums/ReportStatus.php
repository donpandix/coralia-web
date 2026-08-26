<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Open = 'OPEN';
    case InReview = 'IN_REVIEW';
    case Resolved = 'RESOLVED';
    case Dismissed = 'DISMISSED';
}
