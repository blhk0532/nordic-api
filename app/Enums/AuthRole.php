<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthRole: string
{
    case Admin = 'admin';
    case Super = 'super';
    case Manager = 'manager';
    case Service = 'service';
    case Booking = 'booking';
    case Partner = 'partner';
    case Guest = 'guest';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Super => 'Super',
            self::Manager => 'Manager',
            self::Service => 'Service',
            self::Booking => 'Booking',
            self::Partner => 'Partner',
            self::Guest => 'Guest',
            self::User => 'User',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Super => 'warning',
            self::Manager => 'info',
            self::Service => 'success',
            self::Booking => 'primary',
            self::Partner => 'secondary',
            self::Guest => 'gray',
            self::User => 'gray',
        };
    }
}
