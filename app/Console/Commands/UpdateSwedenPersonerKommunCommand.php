<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\UpdateSwedenPersonerAction;
use App\Models\SwedenPersoner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

#[Signature('sweden-personer:update-kommun {--force : Force update even if kommun/lan is already set}')]
#[Description('Update kommun and lan values in the sweden_personer table')]
class UpdateSwedenPersonerKommunCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $force = (bool) $this->option('force');

        $query = SwedenPersoner::query();

        if (! $force) {
            $query->where(fn ($q) => $q->whereNull('kommun')->orWhereNull('lan'));
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No records to update.');

            return;
        }

        $this->info("Updating {$count} records...");

        $progress = progress(
            label: 'Updating Sweden Personer',
            steps: $count,
        );

        $progress->start();

        $query->chunkById(100, function ($records) use ($progress, $force) {
            foreach ($records as $record) {
                UpdateSwedenPersonerAction::execute($record, $force);
                $progress->advance();
            }
        });

        $progress->finish();

        $this->info('Update complete.');
    }
}
