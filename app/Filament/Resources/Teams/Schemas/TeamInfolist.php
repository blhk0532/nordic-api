<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('owner_name')
                            ->label(__('Owner'))
                            ->getStateUsing(fn ($record) => $record->owner()?->name),
                        TextEntry::make('name'),
                        IconEntry::make('is_personal')
                            ->boolean()
                            ->label(__('Personal Team')),
                    ]),
            ]);
    }
}
