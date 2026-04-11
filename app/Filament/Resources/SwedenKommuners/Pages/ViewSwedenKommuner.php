<?php

namespace App\Filament\Resources\SwedenKommuners\Pages;

use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Widgets\KommunViewMapWidget;
use App\Filament\Widgets\KommunViewPostorterMapWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewSwedenKommuner extends ViewRecord
{
    protected static string $resource = SwedenKommunerResource::class;

    protected static ?string $title = 'View Kommun';

    public function getTitle(): string|Htmlable
    {
        return (string) ($record->kommun ?? '');
    }

    protected function getHeaderActions(): array
    {
        return [
            //    EditAction::make(),
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KommunViewMapWidget::class,
            KommunViewPostorterMapWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        $record = $this->getRecord();

        return [
            'kommunName' => (string) ($record->kommun ?? ''),
            'kommunLatitude' => $record->latitude,
            'kommunLongitude' => $record->longitude,
            'kommunPersoner' => $record->personer,
        ];
    }
}
