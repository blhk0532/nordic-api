<?php

declare(strict_types=1);

namespace App\Enums;

enum DialerAttemptStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Ringing = 'ringing';
    case Answered = 'answered';
    case Hangup = 'hangup';
    case Failed = 'failed';
}
