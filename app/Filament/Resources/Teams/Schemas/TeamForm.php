<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique('teams', 'slug', ignoreRecord: true),
                Toggle::make('is_personal')
                    ->label(__('Personal Team')),
            ]);
    }
}
