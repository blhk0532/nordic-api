<?php

declare(strict_types=1);

namespace App\Enums;

enum DialerCampaignStatus: string
{
    case Draft = 'draft';
    case Running = 'running';
    case Paused = 'paused';
    case Stopped = 'stopped';
    case Completed = 'completed';
}
