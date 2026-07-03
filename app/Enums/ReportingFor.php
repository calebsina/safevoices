<?php

namespace App\Enums;

/** Whether the reporter reports about themselves or someone else. */
enum ReportingFor: string
{
    case Self  = 'self';
    case Other = 'other';
}
