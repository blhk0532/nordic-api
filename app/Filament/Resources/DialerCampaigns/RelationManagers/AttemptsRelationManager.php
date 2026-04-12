<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns\RelationManagers;

use App\Models\DialerCampaign;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * @property DialerCampaign $ownerRecord
 */
class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.phone_number')
                    ->label('Lead')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                TextColumn::make('disposition')
                    ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title()->toString() : '—'),
                TextColumn::make('channel')
                    ->toggleable(),
                TextColumn::make('ami_action_id')
                    ->label('Action ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ami_unique_id')
                    ->label('Unique ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
