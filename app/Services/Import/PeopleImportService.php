<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Person;
use Maatwebsite\Excel\Facades\Excel;

class PeopleImportService
{
    /**
     * Import people from a file (CSV or XLSX).
     *
     * @param  string  $filePath  Path to the uploaded file
     * @return int Number of records imported/updated
     */
    public function importFromFile(string $filePath): int
    {
        $rows = Excel::toArray([], $filePath);

        if (empty($rows) || empty($rows[0])) {
            return 0;
        }

        $headers = $this->normalizeHeaders($rows[0][0] ?? []);
        $dataRows = array_slice($rows[0], 1);

        $imported = 0;

        foreach ($dataRows as $row) {
            try {
                $data = $this->mapRowToPersonData($row, $headers);

                if (empty($data)) {
                    continue;
                }

                // Upsert by personnummer first, then by name+address+postnummer
                $query = Person::query();

                if (filled($data['personnummer'] ?? null)) {
                    $existing = $query
                        ->where('personnummer', $data['personnummer'])
                        ->first();
                } else {
                    $existing = $query
                        ->where('personnamn', $data['personnamn'] ?? '')
                        ->where('gatuadress', $data['gatuadress'] ?? '')
                        ->where('postnummer', $data['postnummer'] ?? '')
                        ->first();
                }

                if ($existing) {
                    $existing->update($data);
                } else {
                    Person::create($data);
                }

                $imported++;
            } catch (\Throwable $exception) {
                // Log but continue processing
                report($exception);
            }
        }

        return $imported;
    }

    /**
     * Normalize header row to map column names.
     *
     * @return array Mapping of clean column names to indices
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $mapping = [
            'ID' => 'id',
            'Personnummer' => 'personnummer',
            'Namn' => 'personnamn',
            'Personnamn' => 'personnamn',
            'Förnamn' => 'fornamn',
            'Efternamn' => 'efternamn',
            'Gatuadress' => 'gatuadress',
            'Adress' => 'gatuadress',
            'Postnummer' => 'postnummer',
            'Postort' => 'postort',
            'Kommun' => 'kommun',
            'Län' => 'lan',
            'Telefon' => 'telefonnummer',
            'Telefonnummer' => 'telefonnummer',
            'Källor' => 'sources',
            'Sources' => 'sources',
            'Födelsedag' => 'fodelsedag',
            'Ålder' => 'alder',
            'Kön' => 'kon',
            'Civilstånd' => 'civilstand',
            'E-post' => 'epost',
            'Email' => 'epost',
            'Adressändring' => 'adressandring',
            'Ägandeform' => 'agandeform',
            'Bostadstyp' => 'bostadstyp',
            'Boarea' => 'boarea',
            'Byggår' => 'byggår',
            'Fastighet' => 'fastighet',
        ];

        $result = [];

        foreach ($headerRow as $index => $header) {
            $header = trim((string) $header);
            $dbColumn = $mapping[$header] ?? null;

            if ($dbColumn) {
                $result[$dbColumn] = $index;
            }
        }

        return $result;
    }

    /**
     * Map a data row to Person model attributes.
     *
     * @param  array  $headers  Mapping from field name to column index
     * @return array Person model data
     */
    private function mapRowToPersonData(array $row, array $headers): array
    {
        $data = [];

        foreach ($headers as $field => $index) {
            $value = $row[$index] ?? null;

            if (is_null($value) || $value === '') {
                continue;
            }

            // Handle phone numbers and sources as arrays
            if ($field === 'telefonnummer' || $field === 'sources') {
                if (is_string($value)) {
                    // Split by comma for CSV or handle as-is
                    $value = array_map('trim', explode(',', $value));
                    $value = array_filter($value);
                }
            }

            $data[$field] = $value;
        }

        return $data;
    }
}
