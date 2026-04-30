<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SwedenKommuner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Signature('app:backfill-sweden-kommuner-counts {--only-missing : Update only rows where gator or adresser is NULL}')]
#[Description('Backfill sweden_kommuner.gator and sweden_kommuner.adresser from child tables')]
class BackfillSwedenKommunerCounts extends Command
{
    public function handle(): int
    {
        $onlyMissing = (bool) $this->option('only-missing');

        $this->info('Backfilling sweden_kommuner counters...');

        $gatorCounts = $this->buildKommunCounts('sweden_gator');
        $adresserCounts = $this->buildKommunCounts('sweden_adresser');

        $stats = [
            'processed' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
        ];

        SwedenKommuner::query()
            ->withTrashed()
            ->select(['id', 'kommun', 'gator', 'adresser'])
            ->orderBy('id')
            ->chunkById(200, function (Collection $kommuner) use ($gatorCounts, $adresserCounts, $onlyMissing, &$stats): void {
                foreach ($kommuner as $kommun) {
                    $stats['processed']++;

                    if ($kommun->kommun === null || trim((string) $kommun->kommun) === '') {
                        $stats['skipped']++;

                        continue;
                    }

                    if ($onlyMissing && $kommun->gator !== null && $kommun->adresser !== null) {
                        $stats['skipped']++;

                        continue;
                    }

                    $key = mb_strtolower(trim((string) $kommun->kommun));
                    $newGator = (int) ($gatorCounts[$key] ?? 0);
                    $newAdresser = (int) ($adresserCounts[$key] ?? 0);

                    if ((int) $kommun->gator === $newGator && (int) $kommun->adresser === $newAdresser) {
                        $stats['unchanged']++;

                        continue;
                    }

                    DB::table('sweden_kommuner')
                        ->where('id', $kommun->id)
                        ->update([
                            'gator' => $newGator,
                            'adresser' => $newAdresser,
                            'updated_at' => now(),
                        ]);

                    $stats['updated']++;
                }
            });

        $this->newLine();
        $this->line('Backfill summary:');
        $this->line('  Processed: '.$stats['processed']);
        $this->line('  Updated:   '.$stats['updated']);
        $this->line('  Unchanged: '.$stats['unchanged']);
        $this->line('  Skipped:   '.$stats['skipped']);

        return Command::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function buildKommunCounts(string $table): array
    {
        return DB::table($table)
            ->selectRaw('LOWER(TRIM(kommun)) as kommun_key, COUNT(*) as aggregate')
            ->whereNotNull('kommun')
            ->whereNull('deleted_at')
            ->groupByRaw('LOWER(TRIM(kommun))')
            ->pluck('aggregate', 'kommun_key')
            ->map(fn (mixed $value): int => (int) $value)
            ->all();
    }
}
