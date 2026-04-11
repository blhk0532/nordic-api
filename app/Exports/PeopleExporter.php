<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Person;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PeopleExporter extends Exporter
{
    protected static ?string $model = Person::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('personnamn')
                ->label('Namn'),
            ExportColumn::make('personnummer')
                ->label('Personnummer'),
            ExportColumn::make('fornamn')
                ->label('Förnamn'),
            ExportColumn::make('efternamn')
                ->label('Efternamn'),
            ExportColumn::make('gatuadress')
                ->label('Gatuadress'),
            ExportColumn::make('postnummer')
                ->label('Postnummer'),
            ExportColumn::make('postort')
                ->label('Postort'),
            ExportColumn::make('kommun')
                ->label('Kommun'),
            ExportColumn::make('lan')
                ->label('Län'),
            ExportColumn::make('telefonnummer')
                ->label('Telefonnummer')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', array_filter($state)) : (string) ($state ?? '')),
            ExportColumn::make('sources')
                ->label('Källor')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', array_filter($state)) : (string) ($state ?? '')),
            ExportColumn::make('fodelsedag')
                ->label('Födelsedag'),
            ExportColumn::make('alder')
                ->label('Ålder'),
            ExportColumn::make('kon')
                ->label('Kön'),
            ExportColumn::make('civilstand')
                ->label('Civilstånd'),
            ExportColumn::make('epost')
                ->label('E-post'),
            ExportColumn::make('adressandring')
                ->label('Adressändring'),
            ExportColumn::make('agandeform')
                ->label('Ägandeform'),
            ExportColumn::make('bostadstyp')
                ->label('Bostadstyp'),
            ExportColumn::make('boarea')
                ->label('Boarea'),
            ExportColumn::make('byggår')
                ->label('Byggår'),
            ExportColumn::make('fastighet')
                ->label('Fastighet'),
            ExportColumn::make('created_at')
                ->label('Skapad'),
            ExportColumn::make('updated_at')
                ->label('Uppdaterad'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return "People exporten är klar. {$export->total_rows} rader exporterades.";
    }

    public static function getFailedNotificationBody(Export $export): string
    {
        return "People exporten misslyckades. {$export->total_rows} rader kunde inte exporteras.";
    }
}
