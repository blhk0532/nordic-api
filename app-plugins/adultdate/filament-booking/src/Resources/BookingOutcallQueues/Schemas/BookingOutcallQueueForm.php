<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\BookingOutcallQueues\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingOutcallQueueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('luid'),
                TextInput::make('name'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('street'),
                TextInput::make('city'),
                TextInput::make('maps'),
                TextInput::make('age')
                    ->numeric(),
                TextInput::make('sex'),
                DatePicker::make('dob'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('status'),
                TextInput::make('type'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('result'),
                TextInput::make('attempts')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('service_user_id')
                    ->numeric(),
                TextInput::make('booking_user_id')
                    ->numeric(),
                DateTimePicker::make('start_time'),
                DateTimePicker::make('end_time'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
