<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Filament\Resources\TerminalLogResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MWGuerra\WebTerminal\Models\TerminalLog;

class TerminalLogsStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('web-terminal::terminal.widgets.total_logs'), number_format(TerminalLog::count()))
                ->description(__('web-terminal::terminal.widgets.all_terminal_log_entries'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make(__('web-terminal::terminal.widgets.today'), number_format(TerminalLog::whereDate('created_at', today())->count()))
                ->description(__('web-terminal::terminal.widgets.logs_created_today'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make(__('web-terminal::terminal.widgets.commands'), number_format(TerminalLog::commands()->count()))
                ->description(__('web-terminal::terminal.widgets.total_commands_executed'))
                ->descriptionIcon('heroicon-m-command-line')
                ->color('info'),

            Stat::make(__('web-terminal::terminal.widgets.errors'), number_format(TerminalLog::errors()->count()))
                ->description(__('web-terminal::terminal.widgets.total_error_events'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
