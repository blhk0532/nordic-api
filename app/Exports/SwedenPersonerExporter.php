<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SwedenPersoner;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SwedenPersonerExporter extends Exporter
{
    protected static ?string $model = SwedenPersoner::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('personnamn')
                ->label('Namn'),
            ExportColumn::make('fornamn')
                ->label('Förnamn'),
            ExportColumn::make('efternamn')
                ->label('Efternamn'),
            ExportColumn::make('alder')
                ->label('Ålder'),
            ExportColumn::make('kon')
                ->label('Kön'),
            ExportColumn::make('adress')
                ->label('Adress'),
            ExportColumn::make('postnummer')
                ->label('Postnummer'),
            ExportColumn::make('postort')
                ->label('Postort'),
            ExportColumn::make('kommun')
                ->label('Kommun'),
            ExportColumn::make('telefon')
                ->label('Telefon')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', array_filter($state)) : (string) ($state ?? '')),
            ExportColumn::make('telefonnummer')
                ->label('Telefonnummer')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', array_filter($state)) : (string) ($state ?? '')),
            ExportColumn::make('civilstand')
                ->label('Civilstånd'),
            ExportColumn::make('bostadstyp')
                ->label('Bostadstyp'),
            ExportColumn::make('personer')
                ->label('Hushåll'),
            ExportColumn::make('p2_namn')
                ->label('Person 2 Namn')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[0]['namn'] ?? ''),
            ExportColumn::make('p2_alder')
                ->label('Person 2 Ålder')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[0]['alder'] ?? ''),
            ExportColumn::make('p3_namn')
                ->label('Person 3 Namn')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[1]['namn'] ?? ''),
            ExportColumn::make('p3_alder')
                ->label('Person 3 Ålder')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[1]['alder'] ?? ''),
            ExportColumn::make('p4_namn')
                ->label('Person 4 Namn')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[2]['namn'] ?? ''),
            ExportColumn::make('p4_alder')
                ->label('Person 4 Ålder')
                ->state(fn (SwedenPersoner $record) => $record->hushall_medlemmar[2]['alder'] ?? ''),
            ExportColumn::make('is_hus')
                ->label('Hus')
                ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nej'),
            ExportColumn::make('is_owner')
                ->label('Ägare')
                ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nej'),
            ExportColumn::make('is_active')
                ->label('Aktiv')
                ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nej'),
            ExportColumn::make('is_done')
                ->label('Klar')
                ->formatStateUsing(fn ($state) => $state ? 'Ja' : 'Nej'),
            ExportColumn::make('created_at')
                ->label('Skapad'),
            ExportColumn::make('updated_at')
                ->label('Uppdaterad'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return "SwedenPersoner exporten är klar. {$export->total_rows} rader exporterades.";
    }

    public static function getFailedNotificationBody(Export $export): string
    {
        return "SwedenPersoner exporten misslyckades. {$export->total_rows} rader kunde inte exporteras.";
    }
}
