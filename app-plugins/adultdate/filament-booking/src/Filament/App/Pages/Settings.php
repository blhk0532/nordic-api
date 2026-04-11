<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\App\Pages;

use BackedEnum;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament-auth::app.pages.settings';
}
