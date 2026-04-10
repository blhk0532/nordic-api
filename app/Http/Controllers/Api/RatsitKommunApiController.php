<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RatsitKommun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatsitKommunApiController extends Controller
{
    /**
     * GET /api/ratsit-kommuner/by-name/{kommun}
     * Retrieve a RatsitKommun record by kommun name.
     */
    public function getByName(Request $request, string $kommun): JsonResponse
    {
        $kommun = urldecode($kommun);

        if (empty($kommun)) {
            return response()->json([
                'message' => 'Kommun name is required',
                'data' => null,
            ], 400);
        }

        // Try exact match first
        $record = RatsitKommun::whereRaw('LOWER(kommun) = ?', [strtolower($kommun)])->first();

        // Try partial match if not found
        if (! $record) {
            $record = RatsitKommun::whereRaw('LOWER(kommun) LIKE ?', ['%'.strtolower($kommun).'%'])->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Kommun not found',
                'kommun' => $kommun,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Kommun retrieved successfully',
            'kommun' => $record->kommun,
            'personer_count' => (int) $record->personer_count,
            'foretag_count' => (int) $record->foretag_count,
            'data' => $record,
        ]);
    }

    /**
     * PUT /api/ratsit-kommuner/update/{kommun}
     * Update a RatsitKommun record by kommun name.
     */
    public function update(Request $request, string $kommun): JsonResponse
    {
        $validated = $request->validate([
            'personer_count' => 'nullable|integer|min:0',
            'foretag_count' => 'nullable|integer|min:0',
            'personer_link' => 'nullable|string',
            'foretag_link' => 'nullable|string',
            'personer_postorter' => 'nullable|integer|min:0',
            'foretag_postorter' => 'nullable|integer|min:0',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $kommun = urldecode($kommun);

        // Try exact match first
        $record = RatsitKommun::whereRaw('LOWER(kommun) = ?', [strtolower($kommun)])->first();

        // Try partial match if not found
        if (! $record) {
            $record = RatsitKommun::whereRaw('LOWER(kommun) LIKE ?', ['%'.strtolower($kommun).'%'])->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Kommun not found',
                'kommun' => $kommun,
                'data' => null,
            ], 404);
        }

        $record->update($validated);

        return response()->json([
            'message' => 'Kommun updated successfully',
            'kommun' => $record->kommun,
            'updated_fields' => array_keys($validated),
            'personer_count' => (int) $record->personer_count,
            'foretag_count' => (int) $record->foretag_count,
            'data' => $record,
        ]);
    }

    /**
     * POST /api/ratsit-kommuner/batch-update
     * Bulk update multiple RatsitKommun records.
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $records = $request->validate([
            'records' => 'required|array',
            'records.*.kommun' => 'required|string',
            'records.*.updates' => 'required|array',
        ]);

        $results = [];
        $updated = 0;

        foreach ($records['records'] as $item) {
            $kommunName = $item['kommun'];
            $updates = $item['updates'];

            // Try exact match first
            $record = RatsitKommun::whereRaw('LOWER(kommun) = ?', [strtolower($kommunName)])->first();

            // Try partial match if not found
            if (! $record) {
                $record = RatsitKommun::whereRaw('LOWER(kommun) LIKE ?', ['%'.strtolower($kommunName).'%'])->first();
            }

            if ($record) {
                $record->update($updates);
                $results[] = [
                    'kommun' => $record->kommun,
                    'status' => 'updated',
                ];
                $updated++;
            } else {
                $results[] = [
                    'kommun' => $kommunName,
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
     * GET /api/ratsit-kommuner/list
     * List all RatsitKommun records with their counts.
     */
    public function list(Request $request): JsonResponse
    {
        $query = RatsitKommun::query();

        // Filter by kommun name if provided
        if ($request->has('kommun')) {
            $query->whereRaw('LOWER(kommun) LIKE ?', ['%'.strtolower($request->input('kommun')).'%']);
        }

        $perPage = min($request->input('per_page', 25), 100);
        $records = $query
            ->orderBy('kommun')
            ->paginate($perPage);

        $data = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'kommun' => $record->kommun,
                'personer_count' => (int) $record->personer_count,
                'foretag_count' => (int) $record->foretag_count,
                'personer_link' => $record->personer_link,
                'foretag_link' => $record->foretag_link,
                'updated_at' => $record->updated_at,
            ];
        });

        return response()->json([
            'message' => 'Ratsit kommuner retrieved',
            'total' => $records->total(),
            'per_page' => $records->perPage(),
            'current_page' => $records->currentPage(),
            'last_page' => $records->lastPage(),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/ratsit-kommuner/stats
     * Get aggregated statistics for RatsitKommun records.
     */
    public function stats(Request $request): JsonResponse
    {
        $totalKommuner = RatsitKommun::count();
        $totalPersoner = RatsitKommun::sum('personer_count') ?? 0;
        $totalForetag = RatsitKommun::sum('foretag_count') ?? 0;

        return response()->json([
            'message' => 'Ratsit kommuner statistics',
            'stats' => [
                'total_kommuner' => (int) $totalKommuner,
                'total_personer' => (int) $totalPersoner,
                'total_foretag' => (int) $totalForetag,
                'avg_personer_per_kommun' => $totalKommuner > 0 ? round($totalPersoner / $totalKommuner, 2) : 0,
                'avg_foretag_per_kommun' => $totalKommuner > 0 ? round($totalForetag / $totalKommuner, 2) : 0,
            ],
        ]);
    }
}
