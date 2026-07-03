<?php

namespace App\Enums;

/** Who sent a case message. */
enum SenderType: string
{
    case Reporter = 'reporter';
    case Staff    = 'staff';
    case System   = 'system';
}
