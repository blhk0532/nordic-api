<?php

declare(strict_types=1);

namespace App\Filament\Resources\TerminalLogResource\Pages;

use App\Filament\Resources\TerminalLogResource;
use Filament\Resources\Pages\ListRecords;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Widgets\TerminalLogsStatsOverview;

class ListTerminalLogs extends ListRecords
{
    protected static string $resource = TerminalLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TerminalLogsStatsOverview::class,
        ];
    }
}
