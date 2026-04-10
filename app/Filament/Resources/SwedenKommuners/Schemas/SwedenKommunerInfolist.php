<?php

namespace App\Filament\Resources\SwedenKommuners\Schemas;

use App\Models\SwedenKommuner;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SwedenKommunerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->heading(null)
                            ->schema([
                                TextEntry::make('kommun'),
                                TextEntry::make('lan')
                                    ->label('Landskap'),
                                TextEntry::make('postnummer')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('postorter')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('gator')
                                    ->numeric()
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('adresser')
                                    ->numeric()
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('personer')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('foretag')
                                    ->numeric()
                                    ->placeholder('-'),
                                TextEntry::make('latitude')
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('longitude')
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->dateTime()
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->dateTime()
                                    ->hidden()
                                    ->placeholder('-'),
                                TextEntry::make('deleted_at')
                                    ->dateTime()
                                    ->visible(fn(SwedenKommuner $record): bool => $record->trashed()),
                            ])
                            ->columnSpan('full')
                            ->columns(6),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
