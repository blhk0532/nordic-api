<?php

declare(strict_types=1);

namespace App\Services\GoogleSheets;

use App\Models\Person;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\ClearValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use RuntimeException;

class PeopleSheetsSyncService
{
    /** @var array<int, string> */
    private const HEADER_ROW = [
        'ID',
        'Personnummer',
        'Namn',
        'Förnamn',
        'Efternamn',
        'Adress',
        'Postnummer',
        'Postort',
        'Kommun',
        'Län',
        'Telefon',
        'Källor',
        'Födelsedag',
        'Ålder',
        'Kön',
        'Civilstånd',
        'E-post',
        'Adressändring',
        'Ägandeform',
        'Bostadstyp',
        'Boarea',
        'Byggår',
        'Fastighet',
        'Skapad',
        'Uppdaterad',
    ];

    public function syncRecords(
        Collection $records,
        string $spreadsheetId,
        string $sheetName = 'People',
        bool $replaceExistingRows = false,
    ): int {
        if (blank($spreadsheetId)) {
            throw new RuntimeException('Spreadsheet ID is required.');
        }

        if ($records->isEmpty()) {
            return 0;
        }

        $sheetService = $this->makeSheetsService();
        $this->ensureSheetExists($sheetService, $spreadsheetId, $sheetName);

        $range = sprintf('%s!A:Z', $this->quoteSheetName($sheetName));

        if ($replaceExistingRows) {
            $sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $range,
                new ClearValuesRequest
            );
        }

        $rows = [];

        if ($replaceExistingRows || ! $this->sheetHasData($sheetService, $spreadsheetId, $sheetName)) {
            $rows[] = self::HEADER_ROW;
        }

        /** @var Person $person */
        foreach ($records as $person) {
            $rows[] = $this->personToRow($person);
        }

        $this->appendRows($sheetService, $spreadsheetId, $range, $rows);

        return $records->count();
    }

    public function syncAllPeople(
        string $spreadsheetId,
        string $sheetName = 'People',
        bool $replaceExistingRows = false,
    ): int {
        if (blank($spreadsheetId)) {
            throw new RuntimeException('Spreadsheet ID is required.');
        }

        $sheetService = $this->makeSheetsService();
        $this->ensureSheetExists($sheetService, $spreadsheetId, $sheetName);

        $range = sprintf('%s!A:Z', $this->quoteSheetName($sheetName));

        if ($replaceExistingRows) {
            $sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $range,
                new ClearValuesRequest
            );
        }

        $shouldWriteHeader = $replaceExistingRows || ! $this->sheetHasData($sheetService, $spreadsheetId, $sheetName);

        $syncedCount = 0;

        Person::query()
            ->orderBy('id')
            ->chunk(1000, function (EloquentCollection $records) use (&$shouldWriteHeader, &$syncedCount, $sheetService, $spreadsheetId, $range): void {
                $rows = [];

                if ($shouldWriteHeader) {
                    $rows[] = self::HEADER_ROW;
                    $shouldWriteHeader = false;
                }

                /** @var Person $person */
                foreach ($records as $person) {
                    $rows[] = $this->personToRow($person);
                }

                $this->appendRows($sheetService, $spreadsheetId, $range, $rows);
                $syncedCount += $records->count();
            });

        return $syncedCount;
    }

    public function importIntoDatabase(string $spreadsheetId, string $sheetName = 'People'): int
    {
        if (blank($spreadsheetId)) {
            throw new RuntimeException('Spreadsheet ID is required.');
        }

        $sheetService = $this->makeSheetsService();
        $range = sprintf('%s!A:Z', $this->quoteSheetName($sheetName));

        $response = $sheetService->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues() ?? [];

        if ($values === []) {
            return 0;
        }

        $rows = $this->dropHeaderIfPresent($values);
        $imported = 0;

        foreach ($rows as $row) {
            $personnummer = $this->nullableString($row[1] ?? null);
            $personnamn = $this->nullableString($row[2] ?? null);
            $gatuadress = $this->nullableString($row[5] ?? null);
            $postnummer = $this->nullableString($row[6] ?? null);

            $attributes = [
                'personnummer' => $personnummer,
                'personnamn' => $personnamn,
                'fornamn' => $this->nullableString($row[3] ?? null),
                'efternamn' => $this->nullableString($row[4] ?? null),
                'gatuadress' => $gatuadress,
                'postnummer' => $postnummer,
                'postort' => $this->nullableString($row[7] ?? null),
                'kommun' => $this->nullableString($row[8] ?? null),
                'lan' => $this->nullableString($row[9] ?? null),
                'telefonnummer' => $this->splitCsvList($row[10] ?? null),
                'sources' => $this->splitCsvList($row[11] ?? null),
                'fodelsedag' => $this->nullableString($row[12] ?? null),
                'alder' => $this->nullableString($row[13] ?? null),
                'kon' => $this->nullableString($row[14] ?? null),
                'civilstand' => $this->nullableString($row[15] ?? null),
                'epost_adress' => $this->nullableString($row[16] ?? null),
                'adressandring' => $this->nullableString($row[17] ?? null),
                'agandeform' => $this->nullableString($row[18] ?? null),
                'bostadstyp' => $this->nullableString($row[19] ?? null),
                'boarea' => $this->nullableString($row[20] ?? null),
                'byggar' => $this->nullableString($row[21] ?? null),
                'fastighet' => $this->nullableString($row[22] ?? null),
            ];

            $person = null;

            if (filled($personnummer)) {
                $person = Person::query()->firstOrNew(['personnummer' => $personnummer]);
            } elseif (filled($personnamn) && filled($gatuadress) && filled($postnummer)) {
                $person = Person::query()->firstOrNew([
                    'personnamn' => $personnamn,
                    'gatuadress' => $gatuadress,
                    'postnummer' => $postnummer,
                ]);
            }

            if (! $person) {
                continue;
            }

            $person->fill($attributes);
            $person->save();
            $imported++;
        }

        return $imported;
    }

    private function makeSheetsService(): Sheets
    {
        $credentialsPath = (string) config('services.google_sheets.credentials_json');

        if (! is_file($credentialsPath)) {
            throw new RuntimeException("Google Sheets credentials file not found at: {$credentialsPath}");
        }

        $client = new Client;
        $client->setApplicationName((string) config('app.name', 'Laravel'));
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([Sheets::SPREADSHEETS]);

        $impersonate = config('services.google_sheets.impersonate');

        if (filled($impersonate)) {
            $client->setSubject((string) $impersonate);
        }

        return new Sheets($client);
    }

    private function ensureSheetExists(Sheets $sheetService, string $spreadsheetId, string $sheetName): void
    {
        $spreadsheet = $sheetService->spreadsheets->get(
            $spreadsheetId,
            ['fields' => 'sheets.properties.title']
        );

        $existingSheetNames = collect($spreadsheet->getSheets())
            ->map(fn ($sheet): ?string => $sheet->getProperties()?->getTitle())
            ->filter()
            ->values()
            ->all();

        if (in_array($sheetName, $existingSheetNames, true)) {
            return;
        }

        $sheetService->spreadsheets->batchUpdate(
            $spreadsheetId,
            new BatchUpdateSpreadsheetRequest([
                'requests' => [
                    [
                        'addSheet' => new AddSheetRequest([
                            'properties' => ['title' => $sheetName],
                        ]),
                    ],
                ],
            ])
        );
    }

    private function quoteSheetName(string $sheetName): string
    {
        $escaped = str_replace("'", "''", trim($sheetName));

        return "'{$escaped}'";
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function appendRows(Sheets $sheetService, string $spreadsheetId, string $range, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $sheetService->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            new ValueRange(['values' => $rows]),
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );
    }

    private function sheetHasData(Sheets $sheetService, string $spreadsheetId, string $sheetName): bool
    {
        $response = $sheetService->spreadsheets_values->get(
            $spreadsheetId,
            sprintf('%s!A1:A1', $this->quoteSheetName($sheetName))
        );

        $values = $response->getValues() ?? [];

        return ! empty($values[0][0] ?? null);
    }

    /**
     * @return array<int, mixed>
     */
    private function personToRow(Person $person): array
    {
        return [
            $person->id,
            (string) ($person->personnummer ?? ''),
            (string) ($person->personnamn ?? ''),
            (string) ($person->fornamn ?? ''),
            (string) ($person->efternamn ?? ''),
            (string) ($person->gatuadress ?? ''),
            (string) ($person->postnummer ?? ''),
            (string) ($person->postort ?? ''),
            (string) ($person->kommun ?? ''),
            (string) ($person->lan ?? ''),
            implode(', ', array_filter((array) ($person->telefonnummer ?? []))),
            implode(', ', array_filter((array) ($person->sources ?? []))),
            (string) ($person->fodelsedag ?? ''),
            (string) ($person->alder ?? ''),
            (string) ($person->kon ?? ''),
            (string) ($person->civilstand ?? ''),
            (string) ($person->epost_adress ?? ''),
            (string) ($person->adressandring ?? ''),
            (string) ($person->agandeform ?? ''),
            (string) ($person->bostadstyp ?? ''),
            (string) ($person->boarea ?? ''),
            (string) ($person->byggar ?? ''),
            (string) ($person->fastighet ?? ''),
            optional($person->created_at)?->toDateTimeString() ?? '',
            optional($person->updated_at)?->toDateTimeString() ?? '',
        ];
    }

    /**
     * @param  array<int, array<int, mixed>>  $values
     * @return array<int, array<int, mixed>>
     */
    private function dropHeaderIfPresent(array $values): array
    {
        if (($values[0][0] ?? null) === 'ID') {
            array_shift($values);
        }

        return $values;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) ($value ?? ''));

        return $string === '' ? null : $string;
    }

    /**
     * @return array<int, string>
     */
    private function splitCsvList(mixed $value): array
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $string));

        return array_values(array_filter($parts, fn (string $item): bool => $item !== ''));
    }
}
