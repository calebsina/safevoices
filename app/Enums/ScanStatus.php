<?php

namespace App\Enums;

/** Malware / content scan lifecycle of an evidence item. */
enum ScanStatus: string
{
    case Pending = 'pending';
    case Clean   = 'clean';
    case Flagged = 'flagged';
    case Error   = 'error';
}
