<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Enums;

enum ParticipantRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case PARTICIPANT = 'participant';
}
