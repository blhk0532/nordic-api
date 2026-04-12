<?php

declare(strict_types=1);

namespace App\Enums;

enum DialerLeadStatus: string
{
    case Pending = 'pending';
    case Dialing = 'dialing';
    case Answered = 'answered';
    case NoAnswer = 'no_answer';
    case Busy = 'busy';
    case Failed = 'failed';
    case Completed = 'completed';
}
