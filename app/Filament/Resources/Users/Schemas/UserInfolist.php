<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Anish\TextInputEntry\Infolists\Components\TextInputEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns()
                    ->schema([
                        TextEntry::make('id'),
                        IconEntry::make('status')
                            ->boolean(),
                        TextInputEntry::make('name')
                            ->editable(true)

                            ->rules(['required', 'string', 'max:255'])
                            ->border(false),

                        TextInputEntry::make('email')
                            ->editable(Auth::user()->can('update email'))
                            ->label('Email address')
                            ->rules(['required', 'email'])
                            ->border(false),
                    ]),
            ]);
    }
}
