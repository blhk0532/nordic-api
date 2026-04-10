<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatsitDataRequest;
use App\Http\Requests\UpdateRatsitDataRequest;
use App\Http\Resources\RatsitDataResource;
use App\Models\PrivateData;
use App\Models\RatsitData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;

class RatsitDataController extends Controller
{
    private function normalizePersonnummer(?string $personnummer): ?string
    {
        if (! is_string($personnummer)) {
            return null;
        }

        $trimmed = trim($personnummer);

        if ($trimmed === '') {
            return null;
        }

        return preg_replace('/\s+/', '', $trimmed);
    }

    private function isUsablePersonnummer(?string $personnummer): bool
    {
        if (! is_string($personnummer) || $personnummer === '') {
            return false;
        }

        if (! preg_match('/^[0-9]{8}[-+]?[0-9]{4}$/', $personnummer)) {
            return false;
        }

        return true;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RatsitData::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_queued')) {
            $query->where('is_queued', $request->boolean('is_queued'));
        }

        if ($request->has('postnummer')) {
            $query->where('postnummer', 'like', "%{$request->postnummer}%");
        }

        if ($request->has('postort')) {
            $query->where('postort', 'like', "%{$request->postort}%");
        }

        if ($request->has('kommun')) {
            $query->where('kommun', 'like', "%{$request->kommun}%");
        }

        if ($request->has('lan')) {
            $query->where('lan', 'like', "%{$request->lan}%");
        }

        if ($request->has('personnummer')) {
            $query->where('personnummer', 'like', "%{$request->personnummer}%");
        }

        if ($request->has('personnamn')) {
            $query->where('personnamn', 'like', "%{$request->personnamn}%");
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = min($request->input('per_page', 25), 100000);
        $records = $query->paginate($perPage);

        return RatsitDataResource::collection($records);
    }

    public function store(StoreRatsitDataRequest $request): JsonResponse
    {
        $data = $request->validated();
        $normalizedPersonnummer = $this->normalizePersonnummer($data['personnummer'] ?? null);

        if ($this->isUsablePersonnummer($normalizedPersonnummer)) {
            $data['personnummer'] = $normalizedPersonnummer;
        } else {
            $data['personnummer'] = null;
        }

        // Upsert by personnummer when provided
        if ($this->isUsablePersonnummer($data['personnummer'] ?? null)) {
            $existing = RatsitData::query()
                ->where('personnummer', $data['personnummer'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new RatsitDataResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        // Check for duplicates by gatuadress AND personnamn combined
        // (both fields must be identical to consider it a duplicate)
        if (! empty($data['gatuadress']) && ! empty($data['personnamn'])) {
            $existing = RatsitData::query()
                ->where('gatuadress', $data['gatuadress'])
                ->where('personnamn', $data['personnamn'])
                ->first();

            if ($existing) {
                // Duplicate found: update existing record instead of creating new one
                $existing->update($data);

                return (new RatsitDataResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = RatsitData::create($data);

        return (new RatsitDataResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RatsitData $ratsitData): RatsitDataResource
    {
        return new RatsitDataResource($ratsitData);
    }

    public function update(UpdateRatsitDataRequest $request, RatsitData $ratsit_datum): RatsitDataResource
    {
        $validated = $request->validated();

        Log::info('RatsitDataController update - Starting update', [
            'id' => $ratsit_datum->id,
            'validated_data_keys' => array_keys($validated),
            'validated_data_sample' => [
                'kon' => $validated['kon'] ?? null,
                'telfonnummer_count' => is_array($validated['telfonnummer'] ?? null) ? count($validated['telfonnummer']) : null,
                'epost_adress_count' => is_array($validated['epost_adress'] ?? null) ? count($validated['epost_adress']) : null,
            ],
        ]);

        // Check if model is fillable
        Log::info('RatsitDataController update - Model fillable check', [
            'fillable' => $ratsit_datum->getFillable(),
            'guarded' => $ratsit_datum->getGuarded(),
        ]);

        // Try the update; catch unique constraint violations and resolve them by updating the existing duplicate record
        try {
            $updateResult = $ratsit_datum->update($validated);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            Log::warning('RatsitDataController update - QueryException', ['id' => $ratsit_datum->id, 'message' => $msg]);

            // Detect duplicate key messages (MySQL / MariaDB / PostgreSQL variants)
            if (str_contains($msg, 'Duplicate entry') || str_contains(mb_strtolower($msg), 'unique') || str_contains(mb_strtolower($msg), 'duplicate')) {
                // Try resolving by locating an existing record with same gatuadress and personnamn
                $searchGatuadress = $validated['gatuadress'] ?? null;
                $searchPersonnamn = $validated['personnamn'] ?? null;

                if ($searchGatuadress && $searchPersonnamn) {
                    $existing = RatsitData::query()
                        ->where('gatuadress', $searchGatuadress)
                        ->where('personnamn', $searchPersonnamn)
                        ->first();

                    if ($existing) {
                        Log::info('RatsitDataController update - Merging into existing duplicate', ['incoming_id' => $ratsit_datum->id, 'existing_id' => $existing->id]);
                        $existing->update($validated);

                        // Optionally, mark the $ratsit_datum processed or inactive to avoid double records
                        try {
                            // Reload fresh db state to avoid persisting the incoming mutated attributes that triggered the duplicate
                            $ratsit_datum->refresh();
                        } catch (Throwable $_) {
                            // Ignore refresh errors; we'll still attempt to set flags on the model
                        }
                        $ratsit_datum->is_active = false;
                        $ratsit_datum->is_queued = false;
                        $ratsit_datum->save();
                        $existing->refresh();

                        return new RatsitDataResource($existing);
                    }
                }
            }

            // Re-throw for any other database errors
            throw $e;
        }

        Log::info('RatsitDataController update - Update result', [
            'update_result' => $updateResult,
            'model_has_changes' => $ratsit_datum->isDirty(),
            'dirty_attributes' => $ratsit_datum->getDirty(),
        ]);

        if ($updateResult) {
            // Refresh and return updated model
            $ratsit_datum->refresh();
            Log::info('RatsitDataController update - Success', [
                'refreshed_model' => $ratsit_datum->toArray(),
            ]);

            return new RatsitDataResource($ratsit_datum);
        }
        Log::error('RatsitDataController update - Update failed', [
            'model_errors' => method_exists($ratsit_datum, 'getErrors') ? $ratsit_datum->getErrors() : 'No getErrors method',
            'model_attributes' => $ratsit_datum->getAttributes(),
            'original_attributes' => $ratsit_datum->getOriginal(),
        ]);

        // Return error response instead of resource
        return response()->json([
            'success' => false,
            'message' => 'Failed to update RatsitData',
            'errors' => method_exists($ratsit_datum, 'getErrors') ? $ratsit_datum->getErrors() : null,
        ], 422)->throwResponse();

    }

    public function destroy(RatsitData $ratsitData): JsonResponse
    {
        $ratsitData->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }

    /**
     * Bulk insert/update records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnummer' => 'nullable|string',
            'records.*.personnamn' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.forsamling' => 'nullable|string',
            'records.*.kommun' => 'nullable|string',
            'records.*.kommun_ratsit' => 'nullable|string',
            'records.*.lan' => 'nullable|string',
            'records.*.adressandring' => 'nullable|string',
            'records.*.telfonnummer' => 'nullable|string',
            'records.*.stjarntacken' => 'nullable|string',
            'records.*.fodelsedag' => 'nullable|string',
            'records.*.alder' => 'nullable|string',
            'records.*.kon' => 'nullable|string',
            'records.*.civilstand' => 'nullable|string',
            'records.*.fornamn' => 'nullable|string',
            'records.*.efternamn' => 'nullable|string',
            'records.*.telefon' => 'nullable|string',
            'records.*.epost_adress' => 'nullable',
            'records.*.agandeform' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.boarea' => 'nullable|string',
            'records.*.byggar' => 'nullable|string',
            'records.*.fastighet' => 'nullable|string',
            'records.*.personer' => 'nullable',
            'records.*.foretag' => 'nullable',
            'records.*.grannar' => 'nullable',
            'records.*.fordon' => 'nullable',
            'records.*.hundar' => 'nullable',
            'records.*.bolagsengagemang' => 'nullable',
            'records.*.longitude' => 'nullable|string',
            'records.*.latitud' => 'nullable|string',
            'records.*.google_maps' => 'nullable|string',
            'records.*.google_streetview' => 'nullable|string',
            'records.*.ratsit_se' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.is_queued' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                $normalizedPersonnummer = $this->normalizePersonnummer($recordData['personnummer'] ?? null);

                if ($this->isUsablePersonnummer($normalizedPersonnummer)) {
                    $recordData['personnummer'] = $normalizedPersonnummer;
                } else {
                    $recordData['personnummer'] = null;
                }

                // Convert pipe-separated strings to arrays for fields that should be arrays
                $arrayFields = ['telfonnummer', 'epost_adress', 'bolagsengagemang', 'personer', 'foretag', 'grannar', 'fordon', 'hundar'];
                foreach ($arrayFields as $field) {
                    if (isset($recordData[$field]) && is_string($recordData[$field])) {
                        // Split by pipe and filter out empty values
                        $parts = array_filter(array_map('trim', explode('|', $recordData[$field])));
                        $recordData[$field] = array_values($parts);
                    }
                }

                if ($this->isUsablePersonnummer($recordData['personnummer'] ?? null)) {
                    $record = RatsitData::updateOrCreate(
                        ['personnummer' => $recordData['personnummer']],
                        $recordData
                    );

                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } elseif (! empty($recordData['gatuadress']) && ! empty($recordData['personnamn'])) {
                    // Try finding an existing duplicate by gatuadress + personnamn
                    $existing = RatsitData::query()
                        ->where('gatuadress', $recordData['gatuadress'])
                        ->where('personnamn', $recordData['personnamn'])
                        ->first();

                    if ($existing) {
                        $existing->update($recordData);
                        $updated++;
                    } else {
                        $record = RatsitData::create($recordData);
                        $created++;
                    }
                } else {
                    $record = RatsitData::create($recordData);
                    $created++;
                }

                // Also upsert into private_data for combined Hitta+Ratsit storage.
                try {
                    $privatePayload = $recordData;

                    // Normalize common field names used by PrivateData
                    if (! empty($recordData['personnummer'])) {
                        $privatePayload['ps_personnummer'] = $recordData['personnummer'];
                    }
                    if (! empty($recordData['personnamn'])) {
                        $privatePayload['ps_personnamn'] = $recordData['personnamn'];
                    }
                    if (! empty($recordData['gatuadress'])) {
                        $privatePayload['bo_gatuadress'] = $recordData['gatuadress'];
                    }
                    if (! empty($recordData['postnummer'])) {
                        $privatePayload['bo_postnummer'] = $recordData['postnummer'];
                    }
                    if (! empty($recordData['postort'])) {
                        $privatePayload['bo_postort'] = $recordData['postort'];
                    }
                    if (! empty($recordData['ratsit_se'])) {
                        $privatePayload['ratsit_link'] = $recordData['ratsit_se'];
                    }

                    // Ensure arrays remain arrays (validation above may have converted pipes)
                    $arrayFields = ['telfonnummer', 'epost_adress', 'bolagsengagemang', 'personer', 'foretag', 'grannar', 'fordon', 'hundar'];
                    foreach ($arrayFields as $field) {
                        if (isset($privatePayload[$field]) && is_string($privatePayload[$field])) {
                            $parts = array_filter(array_map('trim', explode('|', $privatePayload[$field])));
                            $privatePayload[$field] = array_values($parts);
                        }
                    }

                    // Upsert by ps_personnummer when available
                    if ($this->isUsablePersonnummer($privatePayload['ps_personnummer'] ?? null)) {
                        PrivateData::updateOrCreate(
                            ['ps_personnummer' => $privatePayload['ps_personnummer']],
                            $privatePayload
                        );
                    } elseif (! empty($privatePayload['bo_gatuadress']) && ! empty($privatePayload['ps_personnamn'])) {
                        // Try match by gatuadress + personnamn
                        $existingPrivate = PrivateData::query()
                            ->where('bo_gatuadress', $privatePayload['bo_gatuadress'])
                            ->where('ps_personnamn', $privatePayload['ps_personnamn'])
                            ->first();

                        if ($existingPrivate) {
                            $existingPrivate->update($privatePayload);
                        } else {
                            PrivateData::create($privatePayload);
                        }
                    } else {
                        // Best-effort create
                        PrivateData::create($privatePayload);
                    }
                } catch (Throwable $_e) {
                    // Log and continue — PrivateData upsert failure should not break the bulk ratsit import
                    Log::warning('private_data upsert failed', ['error' => $_e->getMessage(), 'record' => $recordData['personnummer'] ?? null]);
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();

                // Attempt to resolve duplicate key errors by locating the existing record
                if (str_contains($msg, 'Duplicate entry') || str_contains(mb_strtolower($msg), 'unique') || str_contains(mb_strtolower($msg), 'duplicate')) {
                    try {
                        $candidate = null;

                        if (! empty($recordData['personnummer'])) {
                            $candidate = RatsitData::query()
                                ->where('personnummer', $recordData['personnummer'])
                                ->first();
                        }

                        if (! $candidate && ! empty($recordData['gatuadress']) && ! empty($recordData['personnamn'])) {
                            $candidate = RatsitData::query()
                                ->where('gatuadress', $recordData['gatuadress'])
                                ->where('personnamn', $recordData['personnamn'])
                                ->first();
                        }

                        if ($candidate) {
                            $candidate->update($recordData);
                            $updated++;

                            continue;
                        }
                    } catch (Exception $_e) {
                        // fall through to record as failed below
                        $msg = $_e->getMessage();
                    }
                }

                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnummer' => $recordData['personnummer'] ?? 'unknown',
                    'error' => $msg,
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk operation completed',
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Alias for bulkStore - used by Node.js scripts
     */
    public function bulk(Request $request): JsonResponse
    {
        return $this->bulkStore($request);
    }
}
