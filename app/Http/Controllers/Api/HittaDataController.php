<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHittaDataRequest;
use App\Http\Requests\UpdateHittaDataRequest;
use App\Http\Resources\HittaDataResource;
use App\Models\HittaData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HittaDataController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = HittaData::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_telefon')) {
            $query->where('is_telefon', $request->boolean('is_telefon'));
        }

        if ($request->has('is_ratsit')) {
            $query->where('is_ratsit', $request->boolean('is_ratsit'));
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

        if ($request->has('telefon')) {
            $query->where('telefon', 'like', "%{$request->telefon}%");
        }

        if ($request->has('bostadstyp')) {
            $query->where('bostadstyp', 'like', "%{$request->bostadstyp}%");
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = min($request->input('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return HittaDataResource::collection($records);
    }

    public function store(StoreHittaDataRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Map incoming 'telefonnummer' (scraper name) to DB column 'telefonnumer'
        if (isset($data['telefonnummer'])) {
            // Accept either a string like "num1 | num2" or an array; normalize to array so JSON column stores an array
            if (is_string($data['telefonnummer'])) {
                $parts = array_filter(array_map('trim', explode('|', $data['telefonnummer'])));
                $data['telefonnumer'] = array_values($parts);
            } else {
                $data['telefonnumer'] = $data['telefonnummer'];
            }
            unset($data['telefonnummer']);
        }

        // Prefer upsert by compound key: personnamn + gatuadress (when both present)
        if (! empty($data['personnamn']) && ! empty($data['gatuadress'])) {
            $record = HittaData::updateOrCreate(
                [
                    'personnamn' => $data['personnamn'],
                    'gatuadress' => $data['gatuadress'],
                ],
                $data,
            );

            return (new HittaDataResource($record))
                ->response()
                ->setStatusCode($record->wasRecentlyCreated ? 201 : 200);
        }

        // Fallback: upsert by personnamn when only that is provided
        if (! empty($data['personnamn'])) {
            $record = HittaData::updateOrCreate(
                ['personnamn' => $data['personnamn']],
                $data,
            );

            return (new HittaDataResource($record))
                ->response()
                ->setStatusCode($record->wasRecentlyCreated ? 201 : 200);
        }

        $record = HittaData::create($data);

        return (new HittaDataResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(HittaData $hittaData): HittaDataResource
    {
        return new HittaDataResource($hittaData);
    }

    public function update(UpdateHittaDataRequest $request, HittaData $hittaData): HittaDataResource
    {
        $validated = $request->validated();
        if (isset($validated['telefonnummer'])) {
            if (is_string($validated['telefonnummer'])) {
                $parts = array_filter(array_map('trim', explode('|', $validated['telefonnummer'])));
                $validated['telefonnumer'] = array_values($parts);
            } else {
                $validated['telefonnumer'] = $validated['telefonnummer'];
            }
            unset($validated['telefonnummer']);
        }

        $hittaData->update($validated);

        return new HittaDataResource($hittaData);
    }

    public function destroy(HittaData $hittaData): JsonResponse
    {
        $hittaData->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }

    /**
     * Bulk insert/update records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnamn' => 'nullable|string',
            'records.*.alder' => 'nullable|string',
            'records.*.kon' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.telefon' => 'nullable|string',
            'records.*.telefonnummer' => 'nullable',
            'records.*.karta' => 'nullable|string',
            'records.*.link' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.bostadspris' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.is_telefon' => 'nullable|boolean',
            'records.*.is_ratsit' => 'nullable|boolean',
            'records.*.is_hus' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Support both telefonnummer (incoming) and telefonnumer (DB column)
                if (isset($recordData['telefonnummer'])) {
                    if (is_string($recordData['telefonnummer'])) {
                        $parts = array_filter(array_map('trim', explode('|', $recordData['telefonnummer'])));
                        $recordData['telefonnumer'] = array_values($parts);
                    } else {
                        $recordData['telefonnumer'] = $recordData['telefonnummer'];
                    }
                    unset($recordData['telefonnummer']);
                }
                if (! empty($recordData['personnamn']) && ! empty($recordData['gatuadress'])) {
                    // Prefer compound key upsert when both are present
                    $record = HittaData::updateOrCreate(
                        [
                            'personnamn' => $recordData['personnamn'],
                            'gatuadress' => $recordData['gatuadress'],
                        ],
                        $recordData,
                    );

                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } elseif (! empty($recordData['personnamn'])) {
                    // Fallback to personnamn-only when address is missing
                    $record = HittaData::updateOrCreate(
                        ['personnamn' => $recordData['personnamn']],
                        $recordData,
                    );

                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } else {
                    $record = HittaData::create($recordData);
                    $created++;
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnamn' => $recordData['personnamn'] ?? 'unknown',
                    'error' => $e->getMessage(),
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
