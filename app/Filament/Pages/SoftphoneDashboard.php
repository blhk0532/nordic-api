<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;

class SoftphoneDashboard extends Page
{
    protected string $view = 'filament.pages.softphone-dashboard';

    protected static ?string $navigationLabel = 'Softphone';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';
}
