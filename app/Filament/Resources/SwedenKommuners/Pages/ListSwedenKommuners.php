<?php

namespace App\Filament\Resources\SwedenKommuners\Pages;

use App\Actions\ImportSwedenKommunerCountsFromRatsit;
use App\Filament\Resources\SwedenKommuners\SwedenKommunerResource;
use App\Filament\Widgets\KommunerMapWidget;
use App\Filament\Widgets\LocationMapPickerWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListSwedenKommuners extends ListRecords
{
    protected static string $resource = SwedenKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('importRatsitCounts')
                ->label('Import Ratsit Counts')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Import Ratsit counts for all kommuner')
                ->modalDescription('This imports current personer_count and foretag_count values from ratsit_kommuner into sweden_kommuner without deleting existing rows.')
                ->action(function (ImportSwedenKommunerCountsFromRatsit $importAction): void {
                    try {
                        $stats = $importAction->handle();

                        Notification::make()
                            ->success()
                            ->title('Ratsit counts imported')
                            ->body("Processed {$stats['processed']} rows, updated {$stats['updated']}, unchanged {$stats['unchanged']}, unmatched {$stats['unmatched']}.")
                            ->send();
                    } catch (Throwable $throwable) {
                        Notification::make()
                            ->danger()
                            ->title('Import failed')
                            ->body($throwable->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [

        ];
    }

    protected function getFooterWidgets(): array
    {
        return [

            KommunerMapWidget::make(),
            LocationMapPickerWidget::class,   // Interactive picker
        ];
    }
}
