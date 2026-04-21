<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillSwedenCoordinates
{
    private const DEFAULT_CHUNK_SIZE = 250;

    /** @var array<int, string> */
    private const TABLES = [
        'sweden_adresser',
        'sweden_gator',
        'sweden_personer',
    ];

    /**
     * @param  null|callable(string, int): void  $progressCallback
     * @return array<string, int>
     */
    public function handle(?string $table = null, int $chunkSize = self::DEFAULT_CHUNK_SIZE, ?callable $progressCallback = null): array
    {
        $tables = $table !== null ? [$table] : self::TABLES;
        $stats = [];

        foreach ($tables as $tableName) {
            if (! in_array($tableName, self::TABLES, true)) {
                continue;
            }

            $updated = 0;

            if ($tableName === 'sweden_adresser') {
                $updated += $this->backfillFromRatsitData($tableName, 'adress', $chunkSize, $progressCallback);
            }

            if ($tableName === 'sweden_personer') {
                $updated += $this->backfillFromRatsitData($tableName, 'adress', $chunkSize, $progressCallback);
            }

            $updated += $this->backfillFromSwedenGeoByPostalCode($tableName, $chunkSize, $progressCallback);
            $updated += $this->backfillFromRatsitPostorter($tableName, $chunkSize, $progressCallback);
            $updated += $this->backfillFromSwedenGeoByKommun($tableName, $chunkSize, $progressCallback);
            $updated += $this->backfillFromRatsitKommuner($tableName, $chunkSize, $progressCallback);

            $stats[$tableName] = $updated;
        }

        return $stats;
    }

    private function backfillFromRatsitData(string $tableName, string $addressColumn, int $chunkSize, ?callable $progressCallback): int
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable('ratsit_data')) {
            return 0;
        }

        if (DB::getDriverName() !== 'mysql') {
            $sourceMap = [];

            foreach (DB::table('ratsit_data')
                ->select('gatuadress', 'postnummer', 'postort', 'latitud', 'longitude')
                ->whereNotNull('latitud')
                ->where('latitud', '!=', '')
                ->whereNotNull('longitude')
                ->where('longitude', '!=', '')
                ->cursor() as $sourceRow) {
                $key = $this->buildAddressKey($sourceRow->gatuadress, $sourceRow->postnummer, $sourceRow->postort);

                if ($key === null || isset($sourceMap[$key])) {
                    continue;
                }

                $sourceMap[$key] = [
                    'latitude' => (float) $sourceRow->latitud,
                    'longitude' => (float) $sourceRow->longitude,
                ];
            }

            return $this->applyCoordinateMap(
                tableName: $tableName,
                columns: ['id', $addressColumn, 'postnummer', 'postort', 'latitude', 'longitude'],
                chunkSize: $chunkSize,
                keyResolver: fn (object $row): ?string => $this->buildAddressKey($row->{$addressColumn}, $row->postnummer, $row->postort),
                sourceMap: $sourceMap,
                progressCallback: $progressCallback,
            );
        }

        $updated = 0;

        DB::table($tableName)
            ->select(['id', $addressColumn, 'postnummer', 'postort', 'latitude', 'longitude'])
            ->where(function ($query): void {
                $query->whereNull('latitude')
                    ->orWhereNull('longitude');
            })
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use ($tableName, $addressColumn, $progressCallback, &$updated): void {
                $normalizedAddresses = [];
                $normalizedPostnummers = [];
                $normalizedPostorter = [];

                foreach ($rows as $row) {
                    $normalizedAddress = $this->normalizeValue($row->{$addressColumn});
                    $normalizedPostnummer = $this->normalizePostnummer($row->postnummer);
                    $normalizedPostort = $this->normalizeValue($row->postort);

                    if ($normalizedAddress === null || $normalizedPostnummer === null || $normalizedPostort === null) {
                        continue;
                    }

                    $normalizedAddresses[$normalizedAddress] = true;
                    $normalizedPostnummers[$normalizedPostnummer] = true;
                    $normalizedPostorter[$normalizedPostort] = true;
                }

                if ($normalizedAddresses === [] || $normalizedPostnummers === [] || $normalizedPostorter === []) {
                    return;
                }

                $sourceRows = DB::table('ratsit_data')
                    ->select(['gatuadress', 'postnummer', 'postort', 'latitud', 'longitude'])
                    ->whereNotNull('latitud')
                    ->where('latitud', '!=', '')
                    ->whereNotNull('longitude')
                    ->where('longitude', '!=', '')
                    ->whereIn(DB::raw('UPPER(TRIM(gatuadress))'), array_keys($normalizedAddresses))
                    ->whereIn(DB::raw("REPLACE(TRIM(postnummer), ' ', '')"), array_keys($normalizedPostnummers))
                    ->whereIn(DB::raw('UPPER(TRIM(postort))'), array_keys($normalizedPostorter))
                    ->get();

                $sourceMap = [];

                foreach ($sourceRows as $sourceRow) {
                    $key = $this->buildAddressKey($sourceRow->gatuadress, $sourceRow->postnummer, $sourceRow->postort);

                    if ($key === null || isset($sourceMap[$key])) {
                        continue;
                    }

                    $sourceMap[$key] = [
                        'latitude' => (float) $sourceRow->latitud,
                        'longitude' => (float) $sourceRow->longitude,
                    ];
                }

                if ($sourceMap === []) {
                    return;
                }

                foreach ($rows as $row) {
                    if ($row->latitude !== null && $row->longitude !== null) {
                        continue;
                    }

                    $key = $this->buildAddressKey($row->{$addressColumn}, $row->postnummer, $row->postort);

                    if ($key === null || ! isset($sourceMap[$key])) {
                        continue;
                    }

                    $updates = [];

                    if ($row->latitude === null) {
                        $updates['latitude'] = $sourceMap[$key]['latitude'];
                    }

                    if ($row->longitude === null) {
                        $updates['longitude'] = $sourceMap[$key]['longitude'];
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $updated += DB::table($tableName)
                        ->where('id', $row->id)
                        ->update($updates);

                    if ($progressCallback !== null) {
                        $progressCallback($tableName, $updated);
                    }
                }
            });

        return $updated;
    }

    private function backfillFromSwedenGeoByPostalCode(string $tableName, int $chunkSize, ?callable $progressCallback): int
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable('sweden_geo')) {
            return 0;
        }

        $sourceMap = [];

        foreach (DB::table('sweden_geo')
            ->select('postnummer', 'postort', 'latitude', 'longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->cursor() as $sourceRow) {
            $key = $this->buildPostalKey($sourceRow->postnummer, $sourceRow->postort);

            if ($key === null || isset($sourceMap[$key])) {
                continue;
            }

            $sourceMap[$key] = [
                'latitude' => (float) $sourceRow->latitude,
                'longitude' => (float) $sourceRow->longitude,
            ];
        }

        return $this->applyCoordinateMap(
            tableName: $tableName,
            columns: ['id', 'postnummer', 'postort', 'latitude', 'longitude'],
            chunkSize: $chunkSize,
            keyResolver: fn (object $row): ?string => $this->buildPostalKey($row->postnummer, $row->postort),
            sourceMap: $sourceMap,
            progressCallback: $progressCallback,
        );
    }

    private function backfillFromRatsitPostorter(string $tableName, int $chunkSize, ?callable $progressCallback): int
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable('ratsit_postorter')) {
            return 0;
        }

        $sourceMap = [];

        foreach (DB::table('ratsit_postorter')
            ->select('post_nummer', 'post_ort', 'lat', 'lng')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->cursor() as $sourceRow) {
            $key = $this->buildPostalKey($sourceRow->post_nummer, $sourceRow->post_ort);

            if ($key === null || isset($sourceMap[$key])) {
                continue;
            }

            $sourceMap[$key] = [
                'latitude' => (float) $sourceRow->lat,
                'longitude' => (float) $sourceRow->lng,
            ];
        }

        return $this->applyCoordinateMap(
            tableName: $tableName,
            columns: ['id', 'postnummer', 'postort', 'latitude', 'longitude'],
            chunkSize: $chunkSize,
            keyResolver: fn (object $row): ?string => $this->buildPostalKey($row->postnummer, $row->postort),
            sourceMap: $sourceMap,
            progressCallback: $progressCallback,
        );
    }

    private function backfillFromSwedenGeoByKommun(string $tableName, int $chunkSize, ?callable $progressCallback): int
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable('sweden_geo')) {
            return 0;
        }

        $sourceMap = [];

        foreach (DB::table('sweden_geo')
            ->select('kommun', 'latitude', 'longitude')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->cursor() as $sourceRow) {
            $key = $this->normalizeValue($sourceRow->kommun);

            if ($key === null || isset($sourceMap[$key])) {
                continue;
            }

            $sourceMap[$key] = [
                'latitude' => (float) $sourceRow->latitude,
                'longitude' => (float) $sourceRow->longitude,
            ];
        }

        return $this->applyCoordinateMap(
            tableName: $tableName,
            columns: ['id', 'kommun', 'latitude', 'longitude'],
            chunkSize: $chunkSize,
            keyResolver: fn (object $row): ?string => $this->normalizeValue($row->kommun),
            sourceMap: $sourceMap,
            progressCallback: $progressCallback,
        );
    }

    private function backfillFromRatsitKommuner(string $tableName, int $chunkSize, ?callable $progressCallback): int
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasTable('ratsit_kommuner')) {
            return 0;
        }

        $sourceMap = [];

        foreach (DB::table('ratsit_kommuner')
            ->select('kommun', 'lat', 'lng')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->cursor() as $sourceRow) {
            $key = $this->normalizeValue($sourceRow->kommun);

            if ($key === null || isset($sourceMap[$key])) {
                continue;
            }

            $sourceMap[$key] = [
                'latitude' => (float) $sourceRow->lat,
                'longitude' => (float) $sourceRow->lng,
            ];
        }

        return $this->applyCoordinateMap(
            tableName: $tableName,
            columns: ['id', 'kommun', 'latitude', 'longitude'],
            chunkSize: $chunkSize,
            keyResolver: fn (object $row): ?string => $this->normalizeValue($row->kommun),
            sourceMap: $sourceMap,
            progressCallback: $progressCallback,
        );
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, array{latitude: float, longitude: float}>  $sourceMap
     * @param  callable(object): ?string  $keyResolver
     */
    private function applyCoordinateMap(string $tableName, array $columns, int $chunkSize, callable $keyResolver, array $sourceMap, ?callable $progressCallback): int
    {
        if ($sourceMap === []) {
            return 0;
        }

        return $this->applyCoordinatesByLookup(
            tableName: $tableName,
            columns: $columns,
            chunkSize: $chunkSize,
            resolver: function (object $row) use ($keyResolver, $sourceMap): ?array {
                $key = $keyResolver($row);

                if ($key === null || ! isset($sourceMap[$key])) {
                    return null;
                }

                return $sourceMap[$key];
            },
            progressCallback: $progressCallback,
        );
    }

    /**
     * @param  array<int, string>  $columns
     * @param  callable(object): ?array{latitude: float, longitude: float}  $resolver
     */
    private function applyCoordinatesByLookup(string $tableName, array $columns, int $chunkSize, callable $resolver, ?callable $progressCallback): int
    {
        $updated = 0;

        DB::table($tableName)
            ->select($columns)
            ->where(function ($query): void {
                $query->whereNull('latitude')
                    ->orWhereNull('longitude');
            })
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) use ($tableName, $resolver, $progressCallback, &$updated): void {
                foreach ($rows as $row) {
                    $coordinates = $resolver($row);

                    if ($coordinates === null) {
                        continue;
                    }

                    $updates = [];

                    if ($row->latitude === null) {
                        $updates['latitude'] = $coordinates['latitude'];
                    }

                    if ($row->longitude === null) {
                        $updates['longitude'] = $coordinates['longitude'];
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $updated += DB::table($tableName)
                        ->where('id', $row->id)
                        ->update($updates);

                    if ($progressCallback !== null) {
                        $progressCallback($tableName, $updated);
                    }
                }
            });

        return $updated;
    }

    private function buildAddressKey(mixed $address, mixed $postnummer, mixed $postort): ?string
    {
        $normalizedAddress = $this->normalizeValue($address);
        $postalKey = $this->buildPostalKey($postnummer, $postort);

        if ($normalizedAddress === null || $postalKey === null) {
            return null;
        }

        return $normalizedAddress.'|'.$postalKey;
    }

    private function buildPostalKey(mixed $postnummer, mixed $postort): ?string
    {
        $normalizedPostnummer = $this->normalizePostnummer($postnummer);
        $normalizedPostort = $this->normalizeValue($postort);

        if ($normalizedPostnummer === null || $normalizedPostort === null) {
            return null;
        }

        return $normalizedPostnummer.'|'.$normalizedPostort;
    }

    private function normalizePostnummer(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = preg_replace('/\s+/', '', trim((string) $value));

        if ($normalizedValue === null || $normalizedValue === '') {
            return null;
        }

        return $normalizedValue;
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim((string) $value);

        if ($normalizedValue === '') {
            return null;
        }

        return mb_strtoupper($normalizedValue);
    }
}
