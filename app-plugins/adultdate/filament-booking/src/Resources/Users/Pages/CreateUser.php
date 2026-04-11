<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Users\Pages;

use Adultdate\FilamentBooking\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
