<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\Schemas;

use App\Models\BookingDataLead;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingDataLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Lead Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->required()
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Address Information')
                    ->schema([
                        TextInput::make('street')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->maxLength(100),
                        TextInput::make('state')
                            ->maxLength(100),
                        TextInput::make('zip')
                            ->maxLength(20),
                        TextInput::make('country')
                            ->maxLength(100),
                    ])
                    ->columns(3),

                Section::make('Additional Details')
                    ->schema([
                        DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->maxDate(now()),
                        TextInput::make('age')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(150),
                        Select::make('sex')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ]),
                    ])
                    ->columns(3),

                Section::make('Assignment')
                    ->schema([
                        Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Assign to user'),
                    ])
                    ->columns(1),

                Section::make('Status & Activity')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'interested' => 'Interested',
                                'not_interested' => 'Not Interested',
                                'converted' => 'Converted',
                                'do_not_call' => 'Do Not Call',
                            ])
                            ->default('new')
                            ->required(),
                        TextInput::make('attempt_count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Placeholder::make('last_contacted_at')
                            ->label('Last Contacted')
                            ->content(fn (?BookingDataLead $record): string => ($record && $record->last_contacted_at) ? $record->last_contacted_at->diffForHumans() : 'Never'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }
}
