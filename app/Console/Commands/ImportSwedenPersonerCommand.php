<?php

namespace App\Console\Commands;

use App\Models\HittaData;
use App\Models\Merinfo;
use App\Models\MerinfoData;
use App\Models\RatsitData;
use App\Models\SwedenPersoner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('import:sweden-personer {--source=all : Which source to import (hitta,ratsit,mer,merinfo,all)} {--limit=0 : Limit number of rows per source} {--chunk=1000 : Chunk size for processing}')]
#[Description('Import data from hitta_data, ratsit_data, merinfo_data, and merinfos tables into sweden_personer table.')]
class ImportSwedenPersonerCommand extends Command
{
    protected int $created = 0;

    protected int $updated = 0;

    protected int $failed = 0;

    protected int $processed = 0;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->created = $this->updated = $this->failed = $this->processed = 0;
        $source = $this->option('source');
        $limit = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        $sources = match ($source) {
            'hitta' => ['hitta'],
            'ratsit' => ['ratsit'],
            'merinfo' => ['merinfo'],
            'mer' => ['mer'],
            default => ['hitta', 'ratsit', 'merinfo', 'mer'],
        };

        foreach ($sources as $src) {
            $this->info("Importing from {$src} data...");
            $method = 'import'.ucfirst($src);
            if (method_exists($this, $method)) {
                $this->$method($limit, $chunkSize);
            } else {
                $this->error("No import method for {$src}");
            }
        }

        $this->info('Import completed.');
        $this->info(sprintf(
            'Total: %d processed, %d created, %d updated, %d failed.',
            $this->processed,
            $this->created,
            $this->updated,
            $this->failed
        ));
    }

    private function importHitta(int $limit, int $chunkSize): void
    {
        $query = HittaData::where('is_active', true);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $success = 0;
        $failures = 0;

        $query->chunkById($chunkSize, function ($records) use (&$success, &$failures) {
            foreach ($records as $record) {
                $this->processed++;
                try {
                    $this->importHittaRecord($record);
                    $success++;
                } catch (\Exception $e) {
                    $failures++;
                    $this->failed++;
                    $this->warn("Failed to import Hitta record ID {$record->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Hitta data import finished. Success: {$success}, Failures: {$failures}");
    }

    private function importHittaRecord(HittaData $record): void
    {
        $attributes = $this->mapHittaAttributes($record);
        $this->upsertSwedenPersoner($attributes, $record->link, 'hitta');
    }

    private function mapHittaAttributes(HittaData $record): array
    {
        $personnamn = $record->personnamn;
        [$fornamn, $efternamn] = $this->splitPersonnamn($personnamn);

        $adress = $record->gatuadress;
        $postnummer = $record->postnummer;
        $postort = $record->postort;

        // If postnummer or postort missing, try to parse from gatuadress
        if (empty($postnummer) || empty($postort)) {
            [$parsedStreet, $parsedPostnummer, $parsedPostort] = $this->parseAddress($adress);
            if (empty($postnummer) && $parsedPostnummer) {
                $postnummer = $parsedPostnummer;
            }
            if (empty($postort) && $parsedPostort) {
                $postort = $parsedPostort;
            }
            // Optionally update address to street part
            if ($parsedStreet && $parsedStreet !== $adress) {
                $adress = $parsedStreet;
            }
        }

        $alder = $this->extractAge($record->alder);

        $telefon = $record->telefon;
        $telefonnummer = $record->telefonnummer ?? [];

        $bostadstyp = $record->bostadstyp;

        return [
            'fornamn' => $fornamn,
            'efternamn' => $efternamn,
            'personnamn' => $personnamn,
            'personnummer' => null,
            'kon' => $this->normalizeGender($record->kon),
            'telefon' => $telefon,
            'telefonnummer' => $telefonnummer,
            'adress' => $adress,
            'postnummer' => $postnummer,
            'postort' => $postort,
            'kommun' => null,
            'lan' => null,
            'civilstand' => null,
            'adressandring' => null,
            'bostadstyp' => $bostadstyp,
            'agandeform' => null,
            'boarea' => null,
            'byggar' => null,
            'personer' => null,
            'alder' => $alder,
            'is_hus' => $record->is_hus,
            'is_active' => $record->is_active,
            'hitta_link' => $record->link,
            'hitta_data' => $record->toArray(),
        ];
    }

    private function parseAddress(?string $address): array
    {
        $street = $address;
        $postnummer = null;
        $postort = null;

        if (empty($address)) {
            return [$street, $postnummer, $postort];
        }

        // Swedish postal code pattern: 3 digits, space, 2 digits
        if (preg_match('/\b(\d{3}\s\d{2})\s*(.+)\b/', $address, $matches)) {
            $postnummer = $matches[1];
            $postort = $matches[2];
            $street = trim(str_replace($matches[0], '', $address));
        }

        return [$street, $postnummer, $postort];
    }

    private function importRatsit(int $limit, int $chunkSize): void
    {
        $query = RatsitData::where('is_active', true);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $success = 0;
        $failures = 0;

        $query->chunkById($chunkSize, function ($records) use (&$success, &$failures) {
            foreach ($records as $record) {
                $this->processed++;
                try {
                    $this->importRatsitRecord($record);
                    $success++;
                } catch (\Exception $e) {
                    $failures++;
                    $this->failed++;
                    $this->warn("Failed to import Ratsit record ID {$record->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Ratsit data import finished. Success: {$success}, Failures: {$failures}");
    }

    private function importRatsitRecord(RatsitData $record): void
    {
        $attributes = $this->mapRatsitAttributes($record);
        $this->upsertSwedenPersoner($attributes, $record->ratsit_se, 'ratsit');
    }

    private function mapRatsitAttributes(RatsitData $record): array
    {
        $fornamn = $record->fornamn;
        $efternamn = $record->efternamn;
        $personnamn = $record->personnamn;
        $personnummer = $record->personnummer === '\\N' ? null : $record->personnummer;

        $adress = $record->gatuadress;
        $postnummer = $record->postnummer;
        $postort = $record->postort;
        $kommun = $record->kommun;
        $lan = $record->lan;

        $alder = $this->extractAge($record->alder);

        $telefon = $record->telefon === '\\N' ? null : $record->telefon;
        $telefonnummer = $record->telfonnummer ?? [];

        $bostadstyp = $record->bostadstyp;
        $agandeform = $record->agandeform;
        $boarea = $record->boarea;
        $byggar = $record->byggar;
        $personer = $this->countPersoner($record->personer);

        $adressandring = $record->adressandring;

        return [
            'fornamn' => $fornamn,
            'efternamn' => $efternamn,
            'personnamn' => $personnamn,
            'personnummer' => $personnummer,
            'kon' => $this->normalizeGender($record->kon),
            'telefon' => $telefon,
            'telefonnummer' => $telefonnummer,
            'adress' => $adress,
            'postnummer' => $postnummer,
            'postort' => $postort,
            'kommun' => $kommun,
            'lan' => $lan,
            'civilstand' => $record->civilstand,
            'adressandring' => $adressandring,
            'bostadstyp' => $bostadstyp,
            'agandeform' => $agandeform,
            'boarea' => $boarea,
            'byggar' => $byggar,
            'personer' => $personer,
            'alder' => $alder,
            'is_hus' => $record->is_hus,
            'is_active' => $record->is_active,
            'ratsit_link' => $record->ratsit_se,
            'ratsit_data' => $record->toArray(),
            'latitude' => $record->latitud ? (float) $record->latitud : null,
            'longitude' => $record->longitude ? (float) $record->longitude : null,
        ];
    }

    private function importMerinfo(int $limit, int $chunkSize): void
    {
        $query = MerinfoData::where('is_active', true);

        if ($limit > 0) {
            $query->limit($limit);
        }

        $success = 0;
        $failures = 0;

        $query->chunkById($chunkSize, function ($records) use (&$success, &$failures) {
            foreach ($records as $record) {
                $this->processed++;
                try {
                    $this->importMerinfoRecord($record);
                    $success++;
                } catch (\Exception $e) {
                    $failures++;
                    $this->failed++;
                    $this->warn("Failed to import Merinfo record ID {$record->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Merinfo data import finished. Success: {$success}, Failures: {$failures}");
    }

    private function importMerinfoRecord(MerinfoData $record): void
    {
        $attributes = $this->mapMerinfoAttributes($record);
        $this->upsertSwedenPersoner($attributes, $record->link, 'merinfo');
    }

    private function mapMerinfoAttributes(MerinfoData $record): array
    {
        $personnamn = $record->personnamn;
        [$fornamn, $efternamn] = $this->splitPersonnamn($personnamn);

        $adress = $record->gatuadress;
        $postnummer = $record->postnummer;
        $postort = $record->postort;

        $alder = $record->alder;

        $telefon = $record->telefon;
        $telefonnummer = $record->telefonnummer ?? [];
        $telefoner = $record->telefoner ?? [];

        $bostadstyp = $record->bostadstyp;

        return [
            'fornamn' => $fornamn,
            'efternamn' => $efternamn,
            'personnamn' => $personnamn,
            'personnummer' => $record->personalNumber,
            'kon' => $this->normalizeGender($record->kon),
            'telefon' => $telefon,
            'telefonnummer' => array_merge($telefonnummer, $telefoner),
            'adress' => $adress,
            'postnummer' => $postnummer,
            'postort' => $postort,
            'kommun' => null,
            'lan' => null,
            'civilstand' => null,
            'adressandring' => null,
            'bostadstyp' => $bostadstyp,
            'agandeform' => null,
            'boarea' => null,
            'byggar' => null,
            'personer' => null,
            'alder' => $alder,
            'is_hus' => $record->is_hus,
            'is_active' => $record->is_active,
            'merinfo_link' => $record->link,
            'merinfo_data' => $record->toArray(),
        ];
    }

    private function importMer(int $limit, int $chunkSize): void
    {
        $query = Merinfo::query();

        if ($limit > 0) {
            $query->limit($limit);
        }

        $success = 0;
        $failures = 0;

        $query->chunkById($chunkSize, function ($records) use (&$success, &$failures) {
            foreach ($records as $record) {
                $this->processed++;
                try {
                    $this->importMerRecord($record);
                    $success++;
                } catch (\Exception $e) {
                    $failures++;
                    $this->failed++;
                    $this->warn("Failed to import Mer record ID {$record->id}: {$e->getMessage()}");
                }
            }
        }, 'id');

        $this->info("Mer data import finished. Success: {$success}, Failures: {$failures}");
    }

    private function importMerRecord(Merinfo $record): void
    {
        $attributes = $this->mapMerAttributes($record);
        $this->upsertSwedenPersoner($attributes, $record->url, 'merinfo');
    }

    private function mapMerAttributes(Merinfo $record): array
    {
        $personnamn = $record->name;
        [$fallbackFornamn, $efternamn] = $this->splitPersonnamn($personnamn);
        $fornamn = $record->givenNameOrFirstName ?: $fallbackFornamn;

        $addressData = is_array($record->address) ? $record->address : [];
        $addressItem = array_is_list($addressData) ? ($addressData[0] ?? []) : $addressData;

        $adress = is_array($addressItem) ? ($addressItem['street'] ?? null) : null;
        $postnummer = is_array($addressItem) ? ($addressItem['zip_code'] ?? null) : null;
        $postort = is_array($addressItem) ? ($addressItem['city'] ?? null) : null;

        $phoneData = is_array($record->phone_number) ? $record->phone_number : [];
        $phoneItems = array_is_list($phoneData) ? $phoneData : [$phoneData];
        $telefonnummer = [];

        foreach ($phoneItems as $phoneItem) {
            if (is_array($phoneItem)) {
                foreach (['raw', 'number'] as $key) {
                    $value = $phoneItem[$key] ?? null;
                    if (is_string($value) && $value !== '') {
                        $telefonnummer[] = $value;
                    }
                }
            } elseif (is_string($phoneItem) && $phoneItem !== '') {
                $telefonnummer[] = $phoneItem;
            }
        }

        $telefonnummer = array_values(array_unique($telefonnummer));

        return [
            'fornamn' => $fornamn,
            'efternamn' => $efternamn,
            'personnamn' => $personnamn,
            'personnummer' => $record->personalNumber,
            'kon' => $this->normalizeGender($record->gender),
            'telefon' => $telefonnummer[0] ?? null,
            'telefonnummer' => $telefonnummer,
            'adress' => $adress,
            'postnummer' => $postnummer,
            'postort' => $postort,
            'kommun' => null,
            'lan' => null,
            'civilstand' => null,
            'adressandring' => null,
            'bostadstyp' => null,
            'agandeform' => null,
            'boarea' => null,
            'byggar' => null,
            'personer' => null,
            'alder' => null,
            'is_hus' => $record->is_house,
            'is_active' => true,
            'merinfo_link' => $record->url,
            'merinfo_data' => $record->toArray(),
        ];
    }

    private function upsertSwedenPersoner(array $attributes, ?string $sourceLink, string $source): void
    {
        // Determine unique key
        $personnummer = $attributes['personnummer'] ?? null;
        $adress = $attributes['adress'] ?? null;
        $fornamn = $attributes['fornamn'] ?? null;
        $efternamn = $attributes['efternamn'] ?? null;

        $existing = null;
        if (! empty($personnummer)) {
            $existing = SwedenPersoner::where('personnummer', $personnummer)->first();
        }
        if (! $existing && $adress && $fornamn && $efternamn) {
            $existing = SwedenPersoner::where('adress', $adress)
                ->where('fornamn', $fornamn)
                ->where('efternamn', $efternamn)
                ->first();
        }

        if ($existing) {
            // Update existing record
            $this->mergeAttributes($existing, $attributes, $sourceLink, $source);
            $existing->save();
            $this->updated++;
            $this->line("Updated SwedenPersoner ID: {$existing->id}");
        } else {
            // Create new record
            $record = SwedenPersoner::create($attributes);
            $this->created++;
            $this->line("Created SwedenPersoner ID: {$record->id}");
        }
    }

    private function mergeAttributes(SwedenPersoner $record, array $attributes, ?string $sourceLink, string $source): void
    {
        // Merge simple fields if empty
        foreach ([
            'fornamn',
            'efternamn',
            'personnamn',
            'personnummer',
            'kon',
            'telefon',
            'adress',
            'postnummer',
            'postort',
            'kommun',
            'lan',
            'civilstand',
            'adressandring',
            'bostadstyp',
            'agandeform',
            'boarea',
            'byggar',
            'personer',
            'alder',
            'is_hus',
            'is_active',
            'latitude',
            'longitude',
        ] as $field) {
            if (isset($attributes[$field]) && ($record->{$field} === null || $record->{$field} === '')) {
                $record->{$field} = $attributes[$field];
            }
        }

        // Merge telefonnummer arrays
        if (! empty($attributes['telefonnummer'])) {
            $current = $record->telefonnummer ?? [];
            $new = $attributes['telefonnummer'] ?? [];
            $merged = array_unique(array_merge($current, $new));
            $record->telefonnummer = array_values($merged);
        }

        // Set source link
        $linkField = $source.'_link';
        if ($sourceLink && empty($record->{$linkField})) {
            $record->{$linkField} = $sourceLink;
        }

        // Set source data
        $dataField = $source.'_data';
        if (isset($attributes[$dataField]) && empty($record->{$dataField})) {
            $record->{$dataField} = $attributes[$dataField];
        }
    }

    private function splitPersonnamn(?string $personnamn): array
    {
        if (empty($personnamn)) {
            return [null, null];
        }
        $parts = explode(' ', $personnamn, 2);
        $fornamn = $parts[0] ?? null;
        $efternamn = $parts[1] ?? null;

        return [$fornamn, $efternamn];
    }

    private function extractAge(?string $alder): ?int
    {
        if (empty($alder)) {
            return null;
        }
        if (is_numeric($alder)) {
            return (int) $alder;
        }
        // Match digits
        if (preg_match('/\b(\d+)\b/', $alder, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function normalizeGender(?string $kon): ?string
    {
        if (empty($kon)) {
            return null;
        }
        $kon = strtolower($kon);
        if (in_array($kon, ['man', 'm', 'male'])) {
            return 'Man';
        }
        if (in_array($kon, ['kvinna', 'k', 'female', 'f'])) {
            return 'Kvinna';
        }

        return $kon;
    }

    private function countPersoner($personer): ?int
    {
        if (is_array($personer)) {
            return count($personer);
        }
        if (is_string($personer)) {
            $decoded = json_decode($personer, true);
            if (is_array($decoded)) {
                return count($decoded);
            }
        }

        return null;
    }
}
