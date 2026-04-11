<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Enums;

enum GroupType: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';
}
