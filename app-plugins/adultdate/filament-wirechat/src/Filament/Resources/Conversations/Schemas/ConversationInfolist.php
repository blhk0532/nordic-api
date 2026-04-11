<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations\Schemas;

use AdultDate\FilamentWirechat\Enums\ConversationType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn ($state): string => match ($state instanceof ConversationType ? $state : ConversationType::tryFrom($state ?? '')) {
                                ConversationType::SELF => 'gray',
                                ConversationType::PRIVATE => 'success',
                                ConversationType::GROUP => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('participants_count')
                            ->label('Participants')
                            ->default('0')
                            ->formatStateUsing(fn ($record, $state) => $record->participants()->count()),
                        TextEntry::make('messages_count')
                            ->label('Messages')
                            ->default('0')
                            ->formatStateUsing(fn ($record, $state) => $record->messages()->count()),
                        TextEntry::make('disappearing_duration')
                            ->label('Disappearing Duration')
                            ->formatStateUsing(fn (?int $state): string => $state ? "{$state} minutes" : 'Never'),
                        TextEntry::make('disappearing_started_at')
                            ->label('Disappearing Started At')
                            ->dateTime()
                            ->placeholder('Not started'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make('Last Message')
                    ->schema([
                        TextEntry::make('lastMessage.body')
                            ->label('Body')
                            ->placeholder('No messages yet')
                            ->columnSpanFull(),
                        TextEntry::make('lastMessage.created_at')
                            ->label('Sent At')
                            ->dateTime()
                            ->placeholder('N/A'),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->lastMessage !== null),
            ]);
    }
}
