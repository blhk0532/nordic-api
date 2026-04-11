<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Schemas;

use Adultdate\FilamentBooking\Models\Booking\Customer;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Namn')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('address')
                            ->label('Adress')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('phone')
                            ->label('Telefonnummer')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('email')
                            ->label('Epost adress')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn (?Customer $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->state(fn (Customer $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn (Customer $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Customer $record) => $record === null),
            ])
            ->columns(3);
    }
}
