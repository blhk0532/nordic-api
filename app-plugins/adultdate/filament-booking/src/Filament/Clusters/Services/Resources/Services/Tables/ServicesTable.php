<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_code')
                    ->label('Service Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Pris')
                    ->money('SEK', locale: 'sv')
                    ->sortable(),
                TextColumn::make('time_duration')
                    ->label('Duration')
                    ->suffix(' min')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_available')
                    ->label('Available'),
                TernaryFilter::make('is_visible')
                    ->label('Visible'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
