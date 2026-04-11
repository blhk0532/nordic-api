<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations\Schemas;

use AdultDate\FilamentWirechat\Enums\ConversationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Conversation Type')
                    ->options([
                        ConversationType::SELF->value => 'Self',
                        ConversationType::PRIVATE->value => 'Private',
                        ConversationType::GROUP->value => 'Group',
                    ])
                    ->required()
                    ->native(false)
                    ->disabled(fn ($record) => $record !== null), // Disable on edit
                TextInput::make('disappearing_duration')
                    ->label('Disappearing Duration (minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Leave empty for messages that do not disappear.'),
                DateTimePicker::make('disappearing_started_at')
                    ->label('Disappearing Started At')
                    ->helperText('When the disappearing messages feature was activated.')
                    ->displayFormat('Y-m-d H:i:s')
                    ->timezone('UTC'),
            ]);
    }
}
