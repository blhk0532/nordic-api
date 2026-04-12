<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerLeads\RelationManagers;

use App\Models\DialerLead;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * @property DialerLead $ownerRecord
 */
class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                TextColumn::make('disposition')
                    ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title()->toString() : '—'),
                TextColumn::make('channel')
                    ->toggleable(),
                TextColumn::make('destination'),
                TextColumn::make('hangup_cause')
                    ->toggleable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
