<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DialerCampaignInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Campaign')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                        TextEntry::make('source_channel'),
                        TextEntry::make('context'),
                        TextEntry::make('caller_id'),
                        TextEntry::make('team.name')->label('Team'),
                    ]),
                Section::make('Dialing limits')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('max_concurrent_calls'),
                        TextEntry::make('max_attempts'),
                        TextEntry::make('retry_delay_seconds')
                            ->suffix(' sec'),
                        TextEntry::make('started_at')->dateTime(),
                        TextEntry::make('stopped_at')->dateTime(),
                        TextEntry::make('created_at')->dateTime(),
                    ]),
            ]);
    }
}
