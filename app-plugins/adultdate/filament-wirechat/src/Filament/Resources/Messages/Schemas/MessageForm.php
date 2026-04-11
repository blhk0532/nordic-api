<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages\Schemas;

use AdultDate\FilamentWirechat\Enums\MessageType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('conversation_id')
                    ->label('Conversation')
                    ->relationship('conversation', 'id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Conversation #{$record->id}"),
                RichEditor::make('body')
                    ->label('Message Body'),
                Select::make('type')
                    ->label('Message Type')
                    ->options([
                        MessageType::TEXT->value => 'Text',
                        MessageType::ATTACHMENT->value => 'Attachment',
                    ])
                    ->required()
                    ->native(false),
                DateTimePicker::make('kept_at')
                    ->label('Kept At')
                    ->helperText('When the message was kept from disappearing.')
                    ->displayFormat('Y-m-d H:i:s')
                    ->timezone('UTC'),
            ]);
    }
}
