<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Tables;

use App\Enums\AuthRole;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner')
                    ->label(__('Owner'))
                    ->getStateUsing(fn ($record) => $record->owner()?->name),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->label(__('Users'))
                    ->sortable(),
                ToggleColumn::make('is_personal')
                    ->label(__('Personal Team')),
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn () => filament()->auth()->user()?->role instanceof AuthRole
                            ? filament()->auth()->user()->role === AuthRole::Super
                            : filament()->auth()->user()?->role === 'super'
                    ),
            ]);
    }
}
