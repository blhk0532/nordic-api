<?php

namespace App\Filament\Exports;

use App\Models\SwedenPersoner;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SwedenPersonerExporter extends Exporter
{
    protected static ?string $model = SwedenPersoner::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('fornamn')
                ->label('Förnamn'),
            ExportColumn::make('efternamn')
                ->label('Efternamn'),
            ExportColumn::make('personnamn')
                ->label('Fullständigt namn'),
            ExportColumn::make('personnummer')
                ->label('Personnummer'),
            ExportColumn::make('alder')
                ->label('Ålder'),
            ExportColumn::make('kon')
                ->label('Kön'),
            ExportColumn::make('civilstand')
                ->label('Civilstånd'),
            ExportColumn::make('telefon')
                ->label('Telefon'),
            ExportColumn::make('adress')
                ->label('Adress'),
            ExportColumn::make('postnummer')
                ->label('Postnummer'),
            ExportColumn::make('postort')
                ->label('Postort'),
            ExportColumn::make('kommun')
                ->label('Kommun'),
            ExportColumn::make('lan')
                ->label('Län'),
            ExportColumn::make('bostadstyp')
                ->label('Bostadstyp'),
            ExportColumn::make('agandeform')
                ->label('Ägandeform'),
            ExportColumn::make('boarea')
                ->label('Boarea'),
            ExportColumn::make('byggar')
                ->label('Byggår'),
            ExportColumn::make('personer')
                ->label('Antal boende'),
            ExportColumn::make('is_hus')
                ->label('Är hus'),
            ExportColumn::make('is_owner')
                ->label('Är ägare'),
            ExportColumn::make('ratsit_link')
                ->label('Ratsit Länk'),
            ExportColumn::make('hitta_link')
                ->label('Hitta Länk'),
            ExportColumn::make('merinfo_link')
                ->label('Merinfo Länk'),
            ExportColumn::make('eniro_link')
                ->label('Eniro Länk'),
            ExportColumn::make('upplysning_link')
                ->label('Upplysning Länk'),
            ExportColumn::make('mrkoll_link')
                ->label('Mrkoll Länk'),
            ExportColumn::make('latitude')
                ->label('Latitude'),
            ExportColumn::make('longitude')
                ->label('Longitude'),
            ExportColumn::make('created_at')
                ->label('Skapad'),
            ExportColumn::make('updated_at')
                ->label('Uppdaterad'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sweden personer export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
