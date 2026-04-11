<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Enums;

enum Actions: string
{
    case DELETE = 'delete';
    case ARCHIVE = 'archive';
    case REMOVED_BY_ADMIN = 'removed-by-admin';
}
