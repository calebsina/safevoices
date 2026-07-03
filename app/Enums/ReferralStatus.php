<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Pending   = 'pending';
    case Accepted  = 'accepted';
    case Completed = 'completed';
    case Declined  = 'declined';
}
