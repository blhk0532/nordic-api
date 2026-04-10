<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Postnummer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostnummerApiController
{
    /**
     * Display a listing of post numbers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Postnummer::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'post_nummer');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 50);
        $postnummers = $query->paginate($perPage);

        return response()->json($postnummers);
    }

    /**
     * Display the specified post number.
     */
    public function show(string $postNummer): JsonResponse
    {
        // Normalize postal code: decode URL encoding
        $normalizedPostnummer = urldecode($postNummer);

        // Try to find with the exact format first
        $record = Postnummer::where('post_nummer', $normalizedPostnummer)->first();

        // If not found and doesn't contain space, try with space (555 55 format)
        if (! $record && ! str_contains($normalizedPostnummer, ' ') && mb_strlen($normalizedPostnummer) === 5) {
            $withSpace = mb_substr($normalizedPostnummer, 0, 3).' '.mb_substr($normalizedPostnummer, 3);
            $record = Postnummer::where('post_nummer', $withSpace)->first();
        }

        // If not found and contains space, try without space (55555 format)
        if (! $record && str_contains($normalizedPostnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
            $record = Postnummer::where('post_nummer', $withoutSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Post number not found',
                'post_nummer' => $normalizedPostnummer,
            ], 404);
        }

        return response()->json($record);
    }

    /**
     * Update the specified post number.
     */
    public function update(Request $request, string $postNummer): JsonResponse
    {
        $validated = $request->validate([
            'post_ort' => 'nullable|string|max:255',
            'post_lan' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'progress' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'is_pending' => 'nullable|boolean',
            'is_complete' => 'nullable|boolean',
            // Queue flags
            'hitta_personer_queue' => 'nullable|boolean',
            'ratsit_personer_queue' => 'nullable|boolean',
            'merinfo_personer_queue' => 'nullable|boolean',
            'hitta_foretag_queue' => 'nullable|boolean',
            'ratsit_foretag_queue' => 'nullable|boolean',
            'merinfo_foretag_queue' => 'nullable|boolean',
            // Counters
            'total_count' => 'nullable|integer|min:0',
            'count' => 'nullable|integer|min:0',
            'phone' => 'nullable|integer|min:0',
            'house' => 'nullable|integer|min:0',
            'bolag' => 'nullable|integer|min:0',
            'foretag' => 'nullable|integer|min:0',
            'personer' => 'nullable|integer|min:0',
            'merinfo_personer' => 'nullable|integer|min:0',
            'merinfo_foretag' => 'nullable|integer|min:0',
            'platser' => 'nullable|integer|min:0',
            // Processing fields
            'last_processed_page' => 'nullable|integer|min:0',
            'processed_count' => 'nullable|integer|min:0',
        ]);

        // Normalize postal code
        $normalizedPostnummer = urldecode($postNummer);

        // Try to find with the exact format first
        $record = Postnummer::where('post_nummer', $normalizedPostnummer)->first();

        // If not found and doesn't contain space, try with space
        if (! $record && ! str_contains($normalizedPostnummer, ' ') && mb_strlen($normalizedPostnummer) === 5) {
            $withSpace = mb_substr($normalizedPostnummer, 0, 3).' '.mb_substr($normalizedPostnummer, 3);
            $record = Postnummer::where('post_nummer', $withSpace)->first();
        }

        // If not found and contains space, try without space
        if (! $record && str_contains($normalizedPostnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
            $record = Postnummer::where('post_nummer', $withoutSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Post number not found',
                'post_nummer' => $normalizedPostnummer,
            ], 404);
        }

        $record->update($validated);

        return response()->json([
            'message' => 'Post number updated successfully',
            'data' => $record->fresh(),
        ]);
    }

    /**
     * Bulk update multiple post numbers.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_nummers' => 'required|array',
            'post_nummers.*' => 'string',
            'data' => 'required|array',
            'data.status' => 'nullable|string|max:50',
            'data.progress' => 'nullable|integer|min:0|max:100',
            'data.is_active' => 'nullable|boolean',
            'data.is_pending' => 'nullable|boolean',
            'data.is_complete' => 'nullable|boolean',
        ]);

        $updated = 0;
        $errors = [];

        foreach ($validated['post_nummers'] as $postNummer) {
            $record = Postnummer::where('post_nummer', $postNummer)->first();

            if ($record) {
                $record->update($validated['data']);
                $updated++;
            } else {
                $errors[] = $postNummer;
            }
        }

        return response()->json([
            'message' => "Updated {$updated} post numbers",
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
