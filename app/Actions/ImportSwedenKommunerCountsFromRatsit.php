<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RatsitKommun;
use App\Models\SwedenKommuner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSwedenKommunerCountsFromRatsit
{
    /**
     * @param  array<int>|null  $swedenKommunerIds
     * @return array{processed:int, matched:int, updated:int, unchanged:int, unmatched:int}
     */
    public function handle(?array $swedenKommunerIds = null): array
    {
        $stats = [
            'processed' => 0,
            'matched' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'unmatched' => 0,
        ];

        DB::transaction(function () use ($swedenKommunerIds, &$stats): void {
            $ratsitLookup = $this->buildRatsitLookup();

            SwedenKommuner::query()
                ->withTrashed()
                ->when($swedenKommunerIds !== null && $swedenKommunerIds !== [], function ($query) use ($swedenKommunerIds) {
                    $query->whereKey($swedenKommunerIds);
                })
                ->orderBy('id')
                ->chunkById(200, function ($kommuner) use ($ratsitLookup, &$stats): void {
                    foreach ($kommuner as $kommun) {
                        $stats['processed']++;

                        $match = $this->findMatchingRatsitCounts((string) $kommun->kommun, $ratsitLookup);

                        if ($match === null) {
                            $stats['unmatched']++;

                            continue;
                        }

                        $stats['matched']++;

                        $newPersoner = $match['personer'];
                        $newForetag = $match['foretag'];

                        if ((int) $kommun->personer === $newPersoner && (int) $kommun->foretag === $newForetag) {
                            $stats['unchanged']++;

                            continue;
                        }

                        SwedenKommuner::query()
                            ->withTrashed()
                            ->whereKey($kommun->getKey())
                            ->update([
                                'personer' => $newPersoner,
                                'foretag' => $newForetag,
                            ]);

                        $stats['updated']++;
                    }
                });
        });

        return $stats;
    }

    /**
     * @return array<string, array{personer:int, foretag:int}>
     */
    private function buildRatsitLookup(): array
    {
        $lookup = [];

        RatsitKommun::query()
            ->orderBy('id')
            ->get(['kommun', 'personer_count', 'foretag_count'])
            ->each(function (RatsitKommun $kommun) use (&$lookup): void {
                $payload = [
                    'personer' => (int) $kommun->personer_count,
                    'foretag' => (int) $kommun->foretag_count,
                ];

                foreach ($this->kommunCandidates((string) $kommun->kommun) as $candidate) {
                    if ($candidate === '') {
                        continue;
                    }

                    if (! array_key_exists($candidate, $lookup) || $this->shouldReplaceLookupValue($lookup[$candidate], $payload)) {
                        $lookup[$candidate] = $payload;
                    }
                }
            });

        return $lookup;
    }

    /**
     * @param  array<string, array{personer:int, foretag:int}>  $ratsitLookup
     * @return array{personer:int, foretag:int}|null
     */
    private function findMatchingRatsitCounts(string $kommun, array $ratsitLookup): ?array
    {
        foreach ($this->kommunCandidates($kommun) as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (array_key_exists($candidate, $ratsitLookup)) {
                return $ratsitLookup[$candidate];
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function kommunCandidates(string $kommun): array
    {
        $trimmedKommun = trim($kommun);
        $withoutTrailingS = preg_replace('/s$/u', '', $trimmedKommun) ?: $trimmedKommun;

        return array_values(array_unique(array_filter([
            $this->normalizeKommunName($trimmedKommun),
            $this->normalizeKommunName($withoutTrailingS),
        ])));
    }

    /**
     * @param  array{personer:int, foretag:int}  $current
     * @param  array{personer:int, foretag:int}  $incoming
     */
    private function shouldReplaceLookupValue(array $current, array $incoming): bool
    {
        if ($incoming['foretag'] !== $current['foretag']) {
            return $incoming['foretag'] > $current['foretag'];
        }

        return $incoming['personer'] > $current['personer'];
    }

    private function normalizeKommunName(string $name): string
    {
        return (string) Str::of($name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim();
    }
}
