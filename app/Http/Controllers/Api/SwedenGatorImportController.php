<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwedenGator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SwedenGatorImportController extends Controller
{
    public function importScraped(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:1000',
            'records.*.gata' => 'required|string|max:255',
            'records.*.postnummer' => 'nullable|string|max:50',
            'records.*.postort' => 'nullable|string|max:255',
            'records.*.kommun' => 'nullable|string|max:255',
            'records.*.lan' => 'nullable|string|max:255',
            'records.*.personer' => 'nullable|numeric',
            'records.*.ratsit_link' => 'nullable|string|max:255',
            'records.*.is_queue' => 'nullable|boolean',
            'records.*.is_done' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $record) {
            try {
                $record['is_queue'] = $record['is_queue'] ?? true;
                $record['is_done'] = $record['is_done'] ?? false;

                if (isset($record['personer'])) {
                    $record['personer'] = intval($record['personer']);
                }

                $key = [
                    'gata' => $record['gata'],
                ];

                if (! empty($record['postnummer'])) {
                    $key['postnummer'] = $record['postnummer'];
                }

                if (! empty($record['postort'])) {
                    $key['postort'] = $record['postort'];
                }

                if (! empty($record['kommun'])) {
                    $key['kommun'] = $record['kommun'];
                }

                $model = SwedenGator::updateOrCreate($key, $record);

                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'record' => [
                        'gata' => $record['gata'] ?? null,
                        'postnummer' => $record['postnummer'] ?? null,
                        'postort' => $record['postort'] ?? null,
                    ],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'updated' => $updated,
                'failed' => count($errors),
            ],
            'errors' => $errors,
        ]);
    }
}
