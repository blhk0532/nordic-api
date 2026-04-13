<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwedenPostnummer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SwedenPostnummerQueueController extends Controller
{
    public function next(Request $request): JsonResponse
    {
        $orderDirection = strtolower((string) $request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = SwedenPostnummer::query()
            ->whereNotNull('ratsit_link')
            ->where('ratsit_link', '!=', '')
            ->where('is_done', false)
            ->where('is_queue', true)
            ->orderBy('id', $orderDirection);

        if ($request->filled('postort')) {
            $query->where('postort', $request->input('postort'));
        }

        if ($request->filled('postnummer')) {
            $query->where('postnummer', $request->input('postnummer'));
        }

        if ($request->filled('kommun')) {
            $query->where('kommun', $request->input('kommun'));
        }

        if ($request->filled('lan')) {
            $query->where('lan', $request->input('lan'));
        }

        $row = $query->first();

        return $row ? response()->json(['data' => $row]) : response()->json(['data' => null], 204);
    }

    public function markProcessed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:sweden_postnummer,id',
            'gator' => 'nullable|integer',
            'is_done' => 'nullable|boolean',
            'is_queue' => 'nullable|boolean',
        ]);

        $row = SwedenPostnummer::findOrFail($validated['id']);
        $row->is_done = $validated['is_done'] ?? true;
        $row->is_queue = $validated['is_queue'] ?? false;

        if (array_key_exists('gator', $validated) && $validated['gator'] !== null) {
            $row->gator = $validated['gator'];
        }

        $row->save();

        return response()->json(['success' => true, 'data' => $row]);
    }
}
