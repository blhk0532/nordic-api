<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingDataLeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Lead Information')
                    ->schema([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('phone')->label('Phone'),
                        TextEntry::make('email')->label('Email'),
                    ])
                    ->columns(3),

                Section::make('Address')
                    ->schema([
                        TextEntry::make('street'),
                        TextEntry::make('city'),
                        TextEntry::make('state'),
                        TextEntry::make('zip'),
                        TextEntry::make('country'),
                    ])
                    ->columns(3),

                Section::make('Details')
                    ->schema([
                        TextEntry::make('dob')->label('Date of Birth')->date(),
                        TextEntry::make('age')->label('Age'),
                        TextEntry::make('sex')->label('Gender'),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'new' => 'info',
                                'contacted' => 'warning',
                                'interested' => 'success',
                                'not_interested' => 'danger',
                                'converted' => 'primary',
                                'do_not_call' => 'gray',
                            }),
                    ])
                    ->columns(3),

                Section::make('Assignment & Activity')
                    ->schema([
                        TextEntry::make('assignedTo.name')->label('Assigned To'),
                        TextEntry::make('attempt_count')->label('Attempts'),
                        TextEntry::make('last_contacted_at')->label('Last Contacted')->dateTime(),
                        TextEntry::make('created_at')->label('Created')->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}
