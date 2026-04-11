<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Schemas;

use Adultdate\FilamentBooking\Models\Booking\Client;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
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
                    ->columnSpan(['lg' => fn (?Client $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->state(fn (Client $record): ?string => $record->created_at?->diffForHumans()),

                        TextEntry::make('updated_at')
                            ->label('Last modified at')
                            ->state(fn (Client $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Client $record) => $record === null),
            ])
            ->columns(3);
    }
}
