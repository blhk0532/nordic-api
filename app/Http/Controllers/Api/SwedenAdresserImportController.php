<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwedenAdresser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SwedenAdresserImportController extends Controller
{
    public function importScraped(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:1000',
            'records.*.adress' => 'required|string|max:255',
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
                if (isset($record['personer'])) {
                    $record['personer'] = intval($record['personer']);
                }

                $key = [
                    'adress' => $record['adress'],
                    'postort' => $record['postort'] ?? null,
                    'kommun' => $record['kommun'] ?? null,
                ];

                if (! empty($record['postnummer'])) {
                    $key['postnummer'] = $record['postnummer'];
                }

                $model = SwedenAdresser::updateOrCreate($key, $record);

                if ($model->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'record' => [
                        'adress' => $record['adress'] ?? null,
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
