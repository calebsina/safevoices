<?php

namespace App\Enums;

/** Actor recorded on an audit log entry. */
enum ActorType: string
{
    case User     = 'user';
    case Reporter = 'reporter';
    case System   = 'system';
}
