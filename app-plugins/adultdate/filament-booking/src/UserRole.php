<?php

declare(strict_types=1);

namespace App;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SERVICE = 'service';
    case BOOKING = 'booking';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {

            UserRole::ADMIN => 'Admin',
            UserRole::SERVICE => 'Service',
            UserRole::BOOKING => 'Booking',
            UserRole::USER => 'User',
        };
    }
}
