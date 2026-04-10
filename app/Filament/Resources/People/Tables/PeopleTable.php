<?php

namespace App\Filament\Resources\People\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PeopleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Namn')
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('Kön'),
                TextColumn::make('personal_number')
                    ->label('Personnummer'),
                TextColumn::make('street')
                    ->label('Gata')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('zip')
                    ->label('Postnr')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('city')
                    ->label('Postort')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kommun')
                    ->label('Kommun')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('merinfo.id')
                    ->label('Merinfo')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('merinfo_phone')
                    ->label('Telefon')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
