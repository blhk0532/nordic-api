<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerLeads\Tables;

use App\Enums\DialerLeadStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DialerLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                TextColumn::make('priority')
                    ->sortable(),
                TextColumn::make('attempts_count')
                    ->label('Attempts')
                    ->sortable(),
                TextColumn::make('last_disposition')
                    ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title()->toString() : '—')
                    ->toggleable(),
                TextColumn::make('last_attempted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(DialerLeadStatus::cases())->mapWithKeys(fn (DialerLeadStatus $status): array => [
                        $status->value => str($status->value)->replace('_', ' ')->title()->toString(),
                    ])->all()),
                SelectFilter::make('dialer_campaign_id')
                    ->relationship('campaign', 'name')
                    ->label('Campaign'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
