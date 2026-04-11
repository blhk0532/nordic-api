<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case ATTACHMENT = 'attachment';
}
