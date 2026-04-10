<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerinfoData;
use App\Models\PersonerData;
use App\Models\RatsitData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class MerinfoDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MerinfoData::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('postnummer')) {
            $query->where('postnummer', 'like', "%{$request->postnummer}%");
        }

        if ($request->has('postort')) {
            $query->where('postort', 'like', "%{$request->postort}%");
        }

        if ($request->has('personnamn')) {
            $query->where('personnamn', 'like', "%{$request->personnamn}%");
        }

        $perPage = min($request->input('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'personnamn' => 'nullable|string',
            'alder' => 'nullable|string',
            'kon' => 'nullable|string',
            'gatuadress' => 'nullable|string',
            'postnummer' => 'nullable|string',
            'postort' => 'nullable|string',
            'telefon' => 'nullable',
            'karta' => 'nullable|string',
            'link' => 'nullable|string',
            'bostadstyp' => 'nullable|string',
            'bostadspris' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_telefon' => 'nullable|boolean',
            'is_ratsit' => 'nullable|boolean',
            'is_hus' => 'nullable|boolean',
        ]);

        $telefonData = $this->normalizeTelefonInput($validated['telefon'] ?? null);
        $validated['telefon'] = $telefonData['primary'];

        $hasUniqueKeys = ! empty($validated['personnamn'] ?? null) && ! empty($validated['gatuadress'] ?? null);

        if ($hasUniqueKeys) {
            $record = MerinfoData::updateOrCreate(
                [
                    'personnamn' => $validated['personnamn'],
                    'gatuadress' => $validated['gatuadress'],
                ],
                $validated
            );
        } else {
            $record = MerinfoData::create($validated);
        }

        // Auto-add to ratsit_data if is_hus=true AND is_telefon=true
        if ((bool) ($validated['is_hus'] ?? false) && (bool) ($validated['is_telefon'] ?? false)) {
            try {
                $ratsitData = [
                    'personnamn' => $validated['personnamn'] ?? null,
                    'gatuadress' => $validated['gatuadress'] ?? null,
                    'postnummer' => $validated['postnummer'] ?? null,
                    'postort' => $validated['postort'] ?? null,
                    'alder' => $validated['alder'] ?? null,
                    'kon' => $validated['kon'] ?? null,
                    'telefon' => $telefonData['primary'],
                    'telfonnummer' => ! empty($telefonData['all']) ? implode(' | ', $telefonData['all']) : null,
                    'is_active' => true,
                    'is_queued' => true,
                ];

                // Upsert in ratsit_data by personnamn + gatuadress to prevent duplicates
                RatsitData::updateOrCreate(
                    [
                        'personnamn' => $ratsitData['personnamn'],
                        'gatuadress' => $ratsitData['gatuadress'],
                    ],
                    $ratsitData
                );
            } catch (Exception $ratsitError) {
                // Log ratsit error but don't fail the record creation
                Log::warning('Could not auto-add to ratsit_data in Merinfo store', [
                    'personnamn' => $validated['personnamn'] ?? 'unknown',
                    'gatuadress' => $validated['gatuadress'] ?? 'unknown',
                    'error' => $ratsitError->getMessage(),
                ]);
            }
        }

        $status = $record->wasRecentlyCreated ? 201 : 200;
        $message = $record->wasRecentlyCreated ? 'Record created successfully' : 'Record updated successfully';

        return response()->json([
            'message' => $message,
            'data' => $record,
        ], $status);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);

        return response()->json(['data' => $record]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);

        $validated = $request->validate([
            'personnamn' => 'sometimes|string',
            'alder' => 'nullable|string',
            'kon' => 'nullable|string',
            'gatuadress' => 'nullable|string',
            'postnummer' => 'nullable|string',
            'postort' => 'nullable|string',
            'telefon' => 'nullable',
            'karta' => 'nullable|string',
            'link' => 'nullable|string',
            'bostadstyp' => 'nullable|string',
            'bostadspris' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_telefon' => 'nullable|boolean',
            'is_ratsit' => 'nullable|boolean',
            'is_hus' => 'nullable|boolean',
        ]);

        $telefonData = $this->normalizeTelefonInput($validated['telefon'] ?? null);

        if (array_key_exists('telefon', $validated)) {
            $validated['telefon'] = $telefonData['primary'];
        }

        $record->update($validated);

        // Auto-add to ratsit_data if is_hus=true AND is_telefon=true
        if ((bool) ($record->is_hus ?? false) && (bool) ($record->is_telefon ?? false)) {
            try {
                $ratsitData = [
                    'personnamn' => $record->personnamn ?? null,
                    'gatuadress' => $record->gatuadress ?? null,
                    'postnummer' => $record->postnummer ?? null,
                    'postort' => $record->postort ?? null,
                    'alder' => $record->alder ?? null,
                    'kon' => $record->kon ?? null,
                    'telefon' => $telefonData['primary'] ?? $record->telefon,
                    'telfonnummer' => ! empty($telefonData['all']) ? implode(' | ', $telefonData['all']) : $record->telefon,
                    'is_active' => true,
                    'is_queued' => true,
                ];

                // Upsert in ratsit_data by personnamn + gatuadress to prevent duplicates
                RatsitData::updateOrCreate(
                    [
                        'personnamn' => $ratsitData['personnamn'],
                        'gatuadress' => $ratsitData['gatuadress'],
                    ],
                    $ratsitData
                );
            } catch (Exception $ratsitError) {
                // Log ratsit error but don't fail the record update
                Log::warning('Could not auto-add to ratsit_data in Merinfo update', [
                    'personnamn' => $record->personnamn ?? 'unknown',
                    'gatuadress' => $record->gatuadress ?? 'unknown',
                    'error' => $ratsitError->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $record,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);
        $record->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    /**
     * Bulk insert/update records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('MerinfoDataController debug', [
            'payload_type' => gettype($payload),
            'is_array' => is_array($payload),
            'is_list' => array_is_list($payload),
            'first_key' => array_key_first($payload),
            'has_0' => isset($payload[0]),
            'has_short_uuid' => isset($payload[0]) && isset($payload[0]['short_uuid']),
            'has_results' => isset($payload['results']),
            'has_results_items' => isset($payload['results'][0]['items']),
            'payload_sample' => is_array($payload) && isset($payload[0]) ? json_encode($payload[0]) : (isset($payload['results'][0]['items'][0]) ? json_encode($payload['results'][0]['items'][0]) : 'N/A'),
        ]);

        if (array_is_list($payload) && isset($payload[0]['short_uuid'])) {
            Log::info('MerinfoDataController redirecting to MerinfoController (list format)', [
                'records_count' => count($payload),
            ]);

            return app(MerinfoController::class)->bulkStore($request);
        }

        if (isset($payload['results'][0]['items'][0]['short_uuid'])) {
            Log::info('MerinfoDataController redirecting to MerinfoController (results format)', [
                'records_count' => count($payload['results'][0]['items'] ?? []),
            ]);

            return app(MerinfoController::class)->bulkStore($request);
        }

        Log::info('MerinfoDataController bulkStore called', [
            'records_count' => is_array($request->input('records')) ? count($request->input('records')) : 0,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'payload' => $request->all(),
        ]);

        try {
            $validated = $request->validate([
                'records' => 'required|array|min:1|max:100',
                'records.*.personnamn' => 'nullable|string',
                'records.*.name' => 'nullable|string', // Alternative field name
                'records.*.alder' => 'nullable|string',
                'records.*.dob' => 'nullable|string', // Alternative field name
                'records.*.personnummer' => 'nullable|string', // Alternative field name (Swedish personal number/DOB)
                'records.*.kon' => 'nullable|string',
                'records.*.gatuadress' => 'nullable|string',
                'records.*.address' => 'nullable|string', // Alternative field name
                'records.*.postnummer' => 'nullable|string',
                'records.*.zipCode' => 'nullable|string', // Alternative field name
                'records.*.postort' => 'nullable|string',
                'records.*.city' => 'nullable|string', // Alternative field name
                'records.*.telefon' => 'nullable', // Accept any data type: string, array, etc.
                'records.*.telefoner' => 'nullable|array', // Array of phone numbers
                'records.*.telefonnummer' => 'nullable|array', // Alternative field name for phone numbers
                'records.*.phoneNumber' => 'nullable|string', // Alternative field name
                'records.*.karta' => 'nullable|string',
                'records.*.link' => 'nullable|string',
                'records.*.bostadstyp' => 'nullable|string',
                'records.*.bostadspris' => 'nullable|string',
                'records.*.is_active' => 'nullable|boolean',
                'records.*.is_telefon' => 'nullable|boolean',
                'records.*.is_ratsit' => 'nullable|boolean',
                'records.*.is_hus' => 'nullable|boolean',
            ]);
        } catch (Exception $validationError) {
            Log::error('Merinfo bulkStore validation failed', [
                'error' => $validationError->getMessage(),
            ]);

            throw $validationError;
        }

        $created = 0;
        $updated = 0;
        $failed = 0;
        $ratsitAdded = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Map alternative field names to standard ones
                $mappedData = $this->mapRecordFields($recordData);

                // If we have both personnamn and gatuadress, perform upsert to avoid duplicates
                $hasKeys = ! empty($mappedData['personnamn']) && ! empty($mappedData['gatuadress']);

                if ($hasKeys) {
                    $record = MerinfoData::updateOrCreate(
                        [
                            'personnamn' => $mappedData['personnamn'],
                            'gatuadress' => $mappedData['gatuadress'],
                        ],
                        $mappedData
                    );
                    // Heuristic: if the model was recently created
                    $record->wasRecentlyCreated ? $created++ : $updated++;
                } else {
                    // Fallback: create a new record when keys are missing
                    $record = MerinfoData::create($mappedData);
                    $created++;
                }

                // Auto-add to ratsit_data if is_hus=true AND is_telefon=true
                $isHus = (bool) ($mappedData['is_hus'] ?? false);
                $isTelefon = (bool) ($mappedData['is_telefon'] ?? false);

                if ($isHus && $isTelefon) {
                    try {
                        $ratsitData = [
                            'personnamn' => $mappedData['personnamn'] ?? null,
                            'gatuadress' => $mappedData['gatuadress'] ?? null,
                            'postnummer' => $mappedData['postnummer'] ?? null,
                            'postort' => $mappedData['postort'] ?? null,
                            'alder' => $mappedData['alder'] ?? null,
                            'kon' => $mappedData['kon'] ?? null,
                            'telefon' => $mappedData['telefon'][0] ?? null,
                            'telfonnummer' => is_array($mappedData['telefon'] ?? null) ? implode(' | ', $mappedData['telefon']) : null,
                            'is_active' => true,
                            'is_queued' => true,
                        ];

                        // Upsert in ratsit_data by personnamn + gatuadress to prevent duplicates
                        RatsitData::updateOrCreate(
                            [
                                'personnamn' => $ratsitData['personnamn'],
                                'gatuadress' => $ratsitData['gatuadress'],
                            ],
                            $ratsitData
                        );

                        $ratsitAdded++;
                    } catch (Exception $ratsitError) {
                        // Log ratsit error but don't fail the entire bulk operation
                        Log::warning('Could not auto-add to ratsit_data in bulk Merinfo import', [
                            'personnamn' => $mappedData['personnamn'] ?? 'unknown',
                            'gatuadress' => $mappedData['gatuadress'] ?? 'unknown',
                            'error' => $ratsitError->getMessage(),
                        ]);
                    }
                }

            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnamn' => $recordData['personnamn'] ?? $recordData['name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
                Log::error('Merinfo bulkStore record failed', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'record' => $recordData,
                ]);
            }
        }

        Log::info('Merinfo bulkStore completed', [
            'total' => count($validated['records']),
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'ratsitAdded' => $ratsitAdded,
        ]);

        return response()->json([
            'message' => 'Bulk operation completed',
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
                'ratsitAdded' => $ratsitAdded,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Bulk update merinfo totals for merinfo data records.
     */
    public function bulkUpdateTotals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.id' => 'required|integer|exists:merinfo_data,id',
            'records.*.merinfo_personer_total' => 'nullable|integer',
            'records.*.merinfo_foretag_total' => 'nullable|integer',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            $id = $recordData['id'];
            unset($recordData['id']); // Remove id from update data

            try {
                $record = MerinfoData::findOrFail($id);
                $record->update($recordData);
                $updated++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'id' => $id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk update totals completed',
            'summary' => [
                'total' => count($validated['records']),
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Map alternative field names to standard database field names
     */
    private function mapRecordFields(array $recordData): array
    {
        $mapping = [
            'name' => 'personnamn',
            'dob' => 'alder',
            'personnummer' => 'alder', // Map personnummer to alder field
            'address' => 'gatuadress',
            'zipCode' => 'postnummer',
            'city' => 'postort',
            'phoneNumber' => 'telefon',
        ];

        $mapped = [];
        foreach ($recordData as $key => $value) {
            $mappedKey = $mapping[$key] ?? $key;
            $mapped[$mappedKey] = $value;
        }

        // Handle telefon field: convert telefoner (array) to telefon (string)
        if (isset($mapped['telefoner']) && is_array($mapped['telefoner'])) {
            // Extract phone numbers from telefoner array
            if (! empty($mapped['telefoner'])) {
                // If it's an array of objects, extract the 'raw' or 'number' field
                if (is_array($mapped['telefoner'][0])) {
                    $phones = array_filter(array_map(function ($phone) {
                        if (is_array($phone) && isset($phone['raw'])) {
                            return '+'.$phone['raw'];
                        }
                        if (is_array($phone) && isset($phone['number'])) {
                            return $phone['number'];
                        }

                        return $phone;
                    }, $mapped['telefoner']));
                    $mapped['telefon'] = ! empty($phones) ? reset($phones) : null;
                } else {
                    // Simple array of strings - use first one
                    $mapped['telefon'] = reset($mapped['telefoner']);
                }
            } else {
                $mapped['telefon'] = null;
            }
            unset($mapped['telefoner']); // Remove the array field
        }

        // Clean up telefon field - remove "+undefined" or invalid values
        if (isset($mapped['telefon'])) {
            $telefonData = $this->normalizeTelefonInput($mapped['telefon']);
            $mapped['telefon'] = $telefonData['primary'];

            if ($mapped['telefon'] === '+undefined' || $mapped['telefon'] === 'undefined' || empty($mapped['telefon'])) {
                $mapped['telefon'] = null;
            }
        }

        // Store telefoner array in the telefoner JSON field if present
        if (isset($recordData['telefoner']) && is_array($recordData['telefoner'])) {
            $mapped['telefoner'] = $recordData['telefoner'];
        }

        return $mapped;
    }

    /**
     * @return array{primary: string|null, all: array<int, string>}
     */
    private function normalizeTelefonInput(mixed $telefon): array
    {
        if (is_array($telefon)) {
            $numbers = array_values(array_filter($telefon, static fn (mixed $value): bool => is_string($value) || is_numeric($value)));

            $numbers = array_map(static fn (string|int|float $value): string => (string) $value, $numbers);

            return [
                'primary' => $numbers[0] ?? null,
                'all' => $numbers,
            ];
        }

        if (is_string($telefon) || is_numeric($telefon)) {
            $number = (string) $telefon;

            return [
                'primary' => $number,
                'all' => [$number],
            ];
        }

        return [
            'primary' => null,
            'all' => [],
        ];
    }

    /**
     * Save merinfo data to personer_data table with merinfo_* prefix
     */
    private function saveToPersonerData(MerinfoData $merinfoRecord, array $mappedData): void
    {
        // Prepare personer_data record with merinfo_* prefixed columns
        $personerData = [
            'personnamn' => $mappedData['personnamn'] ?? null,
            'gatuadress' => $mappedData['gatuadress'] ?? null,
            'postnummer' => $mappedData['postnummer'] ?? null,
            'postort' => $mappedData['postort'] ?? null,

            // Merinfo-specific fields with merinfo_* prefix
            'merinfo_data_id' => $merinfoRecord->id,
            'merinfo_personnamn' => $mappedData['personnamn'] ?? null,
            'merinfo_alder' => $mappedData['alder'] ?? null,
            'merinfo_kon' => $mappedData['kon'] ?? null,
            'merinfo_gatuadress' => $mappedData['gatuadress'] ?? null,
            'merinfo_postnummer' => $mappedData['postnummer'] ?? null,
            'merinfo_postort' => $mappedData['postort'] ?? null,
            'merinfo_telefon' => $mappedData['telefon'] ?? null,
            'merinfo_karta' => $mappedData['karta'] ?? null,
            'merinfo_link' => $mappedData['link'] ?? null,
            'merinfo_bostadstyp' => $mappedData['bostadstyp'] ?? null,
            'merinfo_bostadspris' => $mappedData['bostadspris'] ?? null,
            'merinfo_is_active' => $mappedData['is_active'] ?? true,
            'merinfo_is_telefon' => $mappedData['is_telefon'] ?? false,
            'merinfo_is_hus' => $mappedData['is_hus'] ?? true,
            'merinfo_created_at' => now(),
            'merinfo_updated_at' => now(),
        ];

        // Use gatuadress + personnamn as unique identifier
        if (! empty($personerData['gatuadress']) && ! empty($personerData['personnamn'])) {
            PersonerData::updateOrCreate(
                [
                    'gatuadress' => $personerData['gatuadress'],
                    'personnamn' => $personerData['personnamn'],
                ],
                $personerData
            );
        } else {
            // Create new record if no unique identifiers
            PersonerData::create($personerData);
        }
    }
}
