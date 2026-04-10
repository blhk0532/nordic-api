<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPostnummers\Actions;

use App\Jobs\RunRatsitHittaScriptJob;
use App\Models\SwedenPostnummer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RunRatsitHittaAction extends Action
{
    public static function make(?string $name = 'run'): static
    {
        return parent::make($name)
            ->label('Run')
            ->icon('heroicon-o-play')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Run ratsit_hitta.mjs')
            ->modalDescription(fn (SwedenPostnummer $record): string => "This will queue jobs/ratsit_hitta.mjs for postnummer {$record->postnummer} and set personer_merinfo_queue to 1.")
            ->modalSubmitActionLabel('Queue Script')
            ->action(function (SwedenPostnummer $record): void {
                SwedenPostnummer::query()
                    ->whereKey($record->getKey())
                    ->update([
                        'personer_merinfo_queue' => 1,
                    ]);

                RunRatsitHittaScriptJob::dispatch($record->postnummer)
                    ->onConnection(config('queue.default'))
                    ->onQueue('ratsit-hitta');

                Notification::make()
                    ->success()
                    ->title('Script queued')
                    ->body("Queued jobs/ratsit_hitta.mjs for {$record->postnummer}.")
                    ->send();
            });
    }
}
