<?php

namespace App\Filament\Widgets;

use App\Models\MapPin;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction as FilamentEditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MapPinsTableWidget extends TableWidget
{
    protected static string $title = 'Map';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => MapPin::query())
            ->recordTitle('Maps')
            ->collapsedGroupsByDefault()
            ->heading(null)
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('data.city')
                    ->label('Postort')
                    ->searchable(),

                TextColumn::make('data.zip')
                    ->label('Postnummer')
                    ->searchable(),
                TextColumn::make('data.state')
                    ->label('Län')
                    ->searchable(),

                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('data.country')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Land')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                FilamentEditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
