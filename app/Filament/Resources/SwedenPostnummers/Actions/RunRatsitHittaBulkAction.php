<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPostnummers\Actions;

use App\Jobs\RunRatsitHittaScriptJob;
use App\Models\SwedenPostnummer;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class RunRatsitHittaBulkAction extends BulkAction
{
    public static function make(?string $name = 'runRatsitHitta'): static
    {
        return parent::make($name)
            ->label('Run Ratsit Hitta')
            ->icon('heroicon-o-play')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Run ratsit_hitta.mjs for selected records')
            ->modalDescription('This will queue jobs/ratsit_hitta.mjs for all selected postnummer records and set personer_merinfo_queue to 1.')
            ->modalSubmitActionLabel('Queue Scripts')
            ->action(function (Collection $records): void {
                $queued = 0;

                foreach ($records as $record) {
                    SwedenPostnummer::query()
                        ->whereKey($record->getKey())
                        ->update([
                            'personer_merinfo_queue' => 1,
                        ]);

                    RunRatsitHittaScriptJob::dispatch((string) $record->postnummer)
                        ->onConnection(config('queue.default'))
                        ->onQueue('ratsit-hitta');

                    $queued++;
                }

                Notification::make()
                    ->success()
                    ->title('Scripts queued')
                    ->body("Queued jobs/ratsit_hitta.mjs for {$queued} record(s).")
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
