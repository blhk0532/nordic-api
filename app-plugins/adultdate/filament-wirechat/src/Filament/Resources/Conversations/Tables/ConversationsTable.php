<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations\Tables;

use AdultDate\FilamentWirechat\Enums\ConversationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state): string => match ($state instanceof ConversationType ? $state : ConversationType::tryFrom($state ?? '')) {
                        ConversationType::SELF => 'gray',
                        ConversationType::PRIVATE => 'success',
                        ConversationType::GROUP => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('participants')
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('lastMessage.body')
                    ->label('Last Message')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->lastMessage?->body)
                    ->default('No messages'),
                TextColumn::make('disappearing_duration')
                    ->label('Disappearing Duration')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} minutes" : 'Never')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'self' => 'Self',
                        'private' => 'Private',
                        'group' => 'Group',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
