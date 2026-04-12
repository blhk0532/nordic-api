<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerLeads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DialerLeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Lead')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('phone_number'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                        TextEntry::make('priority'),
                        TextEntry::make('attempts_count'),
                        TextEntry::make('last_disposition')
                            ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title()->toString() : '—'),
                    ]),
                Section::make('Campaign context')
                    ->columns(2)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('campaign.name')->label('Campaign'),
                        TextEntry::make('team.name')->label('Team'),
                        TextEntry::make('last_attempted_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                    ]),
            ]);
    }
}
