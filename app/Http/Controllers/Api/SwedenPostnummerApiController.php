<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HittaData;
use App\Models\MerinfoData;
use App\Models\RatsitData;
use App\Models\SwedenPostnummer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SwedenPostnummerApiController extends Controller
{
    /**
     * GET /api/sweden-postnummer/get-queue
     * Return first SwedenPostnummer where any *_queue = 1.
     * Similar to job-queue/get-merinfo-postnummer.
     */
    public function getQueue(Request $request): JsonResponse
    {
        $record = SwedenPostnummer::where('personer_hitta_queue', 1)
            ->orWhere('personer_merinfo_queue', 1)
            ->orWhere('personer_ratsit_queue', 1)
            ->orderBy('id', 'asc')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No postnr in queue',
                'postnummer' => null,
            ], 404);
        }

        // Clear the queue flags that were set
        $updates = [];
        if ($record->personer_hitta_queue) {
            $updates['personer_hitta_queue'] = 0;
        }
        if ($record->personer_merinfo_queue) {
            $updates['personer_merinfo_queue'] = 0;
        }
        if ($record->personer_ratsit_queue) {
            $updates['personer_ratsit_queue'] = 0;
        }

        SwedenPostnummer::query()
            ->where('id', $record->id)
            ->update($updates);

        return response()->json([
            'message' => 'Found sweden postnummer with queue flags',
            'postnummer' => $record->post_nummer,
            'kommun' => $record->kommun,
            'post_ort' => $record->post_ort,
            'lan' => $record->lan,
            'data' => $record,
        ]);
    }

    /**
     * GET /api/sweden-postnummer/by-code/{postnummer}
     * Retrieve a SwedenPostnummer record by postal code.
     * Handles formats: "555 55", "55555", "555%2055"
     * Similar to post-nums/by-code/{postnummer}.
     */
    public function getByCode(Request $request, string $postnummer): JsonResponse
    {
        // Clean the postnummer input - handle URL encoding
        $postnummer = urldecode($postnummer);

        if (empty($postnummer)) {
            return response()->json([
                'message' => 'Postnummer is required',
                'data' => null,
            ], 400);
        }

        // Try to find the record - first with spaces as provided, then without spaces
        $record = SwedenPostnummer::where('post_nummer', $postnummer)->first();

        // If not found and contains space, try without space
        if (! $record && str_contains($postnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $postnummer);
            $record = SwedenPostnummer::where('post_nummer', $withoutSpace)->first();
        }

        // If not found and no space, try with space (assuming 5 digits)
        if (! $record && ! str_contains($postnummer, ' ') && mb_strlen($postnummer) === 5) {
            $withSpace = mb_substr($postnummer, 0, 3).' '.mb_substr($postnummer, 3);
            $record = SwedenPostnummer::where('post_nummer', $withSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Postnummer not found',
                'postnummer' => $postnummer,
                'data' => null,
            ], 404);
        }

        // Handle optional update via query parameters
        $updated = false;
        if ($request->has('update') && $request->has('value')) {
            $field = $request->input('update');
            $value = $request->input('value');

            // Validate that the field exists and is fillable
            if (in_array($field, $record->getFillable())) {
                // Type conversion based on field
                if (str_contains($field, '_queue') || str_contains($field, '_saved') || str_contains($field, '_total')) {
                    $value = (int) $value;
                }

                $record->update([$field => $value]);
                $updated = true;
            }
        }

        return response()->json([
            'message' => $updated ? 'Record retrieved and updated' : 'Record retrieved successfully',
            'postnummer' => $record->post_nummer,
            'updated' => $updated,
            'data' => $record,
        ]);
    }

    /**
     * PUT /api/sweden-postnummer/update/{postnummer}
     * Update a SwedenPostnummer record by postal code.
     * Handles formats: "555 55", "55555", "555%2055"
     * Similar to post-nums/update/{postnummer}.
     */
    public function update(Request $request, string $postnummer): JsonResponse
    {
        $validated = $request->validate([
            'kommune' => 'nullable|string',
            'post_ort' => 'nullable|string',
            'lan' => 'nullable|string',
            'personer_hitta_total' => 'nullable|integer',
            'personer_hitta_saved' => 'nullable|integer',
            'personer_hitta_queue' => 'nullable|boolean',
            'personer_merinfo_total' => 'nullable|integer',
            'personer_merinfo_saved' => 'nullable|integer',
            'personer_merinfo_queue' => 'nullable|boolean',
            'personer_ratsit_total' => 'nullable|integer',
            'personer_ratsit_saved' => 'nullable|integer',
            'personer_ratsit_queue' => 'nullable|boolean',
        ]);

        // Normalize postal code: decode URL encoding and handle different formats
        $normalizedPostnummer = urldecode($postnummer);

        // Try to find with the exact format first (with space if provided)
        $record = SwedenPostnummer::where('post_nummer', $normalizedPostnummer)->first();

        // If not found and doesn't contain space, try with space (555 55 format)
        if (! $record && ! str_contains($normalizedPostnummer, ' ') && mb_strlen($normalizedPostnummer) === 5) {
            $withSpace = mb_substr($normalizedPostnummer, 0, 3).' '.mb_substr($normalizedPostnummer, 3);
            $record = SwedenPostnummer::where('post_nummer', $withSpace)->first();
        }

        // If not found and contains space, try without space (55555 format)
        if (! $record && str_contains($normalizedPostnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
            $record = SwedenPostnummer::where('post_nummer', $withoutSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Postnummer not found',
                'postnummer' => $normalizedPostnummer,
                'data' => null,
            ], 404);
        }

        $record->update($validated);

        return response()->json([
            'message' => 'Postnummer updated successfully',
            'postnummer' => $record->post_nummer,
            'updated_fields' => array_keys($validated),
            'data' => $record,
        ]);
    }

    /**
     * POST /api/sweden-postnummer/batch-update
     * Bulk update multiple SwedenPostnummer records.
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $records = $request->validate([
            'records' => 'required|array',
            'records.*.postnummer' => 'required|string',
            'records.*.updates' => 'required|array',
        ]);

        $results = [];
        $updated = 0;

        foreach ($records['records'] as $item) {
            $postnummer = $item['postnummer'];
            $updates = $item['updates'];

            $record = SwedenPostnummer::where('post_nummer', $postnummer)->first();

            if (! $record) {
                $withoutSpace = str_replace(' ', '', $postnummer);
                $record = SwedenPostnummer::where('post_nummer', $withoutSpace)->first();
            }

            if (! $record && ! str_contains($postnummer, ' ') && mb_strlen($postnummer) === 5) {
                $withSpace = mb_substr($postnummer, 0, 3).' '.mb_substr($postnummer, 3);
                $record = SwedenPostnummer::where('post_nummer', $withSpace)->first();
            }

            if ($record) {
                $record->update($updates);
                $results[] = [
                    'postnummer' => $record->post_nummer,
                    'status' => 'updated',
                ];
                $updated++;
            } else {
                $results[] = [
                    'postnummer' => $postnummer,
                    'status' => 'not_found',
                ];
            }
        }

        return response()->json([
            'message' => "Updated {$updated} out of ".count($records['records']).' records',
            'total' => count($records['records']),
            'updated' => $updated,
            'results' => $results,
        ]);
    }

    /**
     * GET /api/sweden-postnummer/check-counts/{postnummer}
     * Check database counts from hitta_data, merinfo_data, ratsit_data tables
     * and return the counts without updating the record.
     */
    public function checkCounts(Request $request, string $postnummer): JsonResponse
    {
        $normalizedPostnummer = urldecode($postnummer);

        // Try to find the record
        $record = SwedenPostnummer::where('post_nummer', $normalizedPostnummer)->first();

        if (! $record && ! str_contains($normalizedPostnummer, ' ') && mb_strlen($normalizedPostnummer) === 5) {
            $withSpace = mb_substr($normalizedPostnummer, 0, 3).' '.mb_substr($normalizedPostnummer, 3);
            $record = SwedenPostnummer::where('post_nummer', $withSpace)->first();
        }

        if (! $record && str_contains($normalizedPostnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
            $record = SwedenPostnummer::where('post_nummer', $withoutSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Postnummer not found',
                'postnummer' => $normalizedPostnummer,
                'data' => null,
            ], 404);
        }

        $postNummer = (string) $record->post_nummer;
        $normalizedPostNummer = str_replace(' ', '', $postNummer);

        $hittaCount = HittaData::query()
            ->where('postnummer', $postNummer)
            ->count();

        $merinfoCount = MerinfoData::query()
            ->where('postnummer', $normalizedPostNummer)
            ->count();

        $ratsitCount = RatsitData::query()
            ->where('postnummer', $postNummer)
            ->count();

        return response()->json([
            'message' => 'Database counts retrieved',
            'postnummer' => $record->post_nummer,
            'counts' => [
                'hitta_data' => $hittaCount,
                'merinfo_data' => $merinfoCount,
                'ratsit_data' => $ratsitCount,
            ],
            'current_saved' => [
                'personer_hitta_saved' => $record->personer_hitta_saved,
                'personer_merinfo_saved' => $record->personer_merinfo_saved,
                'personer_ratsit_saved' => $record->personer_ratsit_saved,
            ],
            'data' => $record,
        ]);
    }
}
