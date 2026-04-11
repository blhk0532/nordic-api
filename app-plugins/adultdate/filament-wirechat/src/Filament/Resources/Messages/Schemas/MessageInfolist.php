<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages\Schemas;

use AdultDate\FilamentWirechat\Enums\MessageType;
use AdultDate\FilamentWirechat\Filament\Resources\Conversations\ConversationResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('conversation_id')
                            ->label('Conversation ID')
                            ->url(fn ($record) => ConversationResource::getUrl('view', ['record' => $record->conversation_id]))
                            ->color('primary'),
                        TextEntry::make('body')
                            ->label('Message Body')
                            ->columnSpanFull()
                            ->placeholder('No message body'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color(fn ($state): string => match ($state instanceof MessageType ? $state : MessageType::tryFrom($state ?? '')) {
                                MessageType::TEXT => 'gray',
                                MessageType::ATTACHMENT => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('sendable_type')
                            ->label('Sender Type')
                            ->formatStateUsing(fn ($state) => class_basename($state)),
                        TextEntry::make('sendable.name')
                            ->label('Sender')
                            ->default('Unknown'),
                        TextEntry::make('reply.body')
                            ->label('Reply To Message')
                            ->placeholder('Not a reply')
                            ->columnSpanFull(),
                        TextEntry::make('kept_at')
                            ->label('Kept At')
                            ->dateTime()
                            ->placeholder('Not kept'),
                        TextEntry::make('created_at')
                            ->label('Sent At')
                            ->dateTime(),
                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime()
                            ->placeholder('Not deleted'),
                    ])
                    ->columns(2),
            ]);
    }
}
