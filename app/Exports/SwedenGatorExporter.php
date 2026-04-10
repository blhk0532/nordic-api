<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SwedenGator;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SwedenGatorExporter extends Exporter
{
    protected static ?string $model = SwedenGator::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
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
