<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource;
use MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Widgets\TerminalLogsStatsOverview;
use Illuminate\Contracts\Support\Htmlable;

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


        public function getHeading(): string|Htmlable|null
    {
        return null;
    }

}
