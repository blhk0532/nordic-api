<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwedenPersoner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Support\Facades\Excel;

class SwedenPersonerImportController extends Controller
{
    // JSON import: expects array of records
    public function importJson(Request $request): JsonResponse
    {
        $data = $request->input('data');
        if (! is_array($data)) {
            return response()->json(['error' => 'Invalid data format, expected array'], 422);
        }

        $rules = [
            '*.personnummer' => 'required|string',
            '*.fornamn' => 'required|string',
            '*.efternamn' => 'required|string',
            // Add more field rules as needed
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $created = 0;
        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                SwedenPersoner::create($row);
                $created++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'created' => $created]);
    }

    public function importScraped(Request $request): JsonResponse
    {
        $data = $request->all();

        if (isset($data['records']) && is_array($data['records'])) {
            foreach ($data['records'] as $index => $record) {
                if (isset($record['alder']) && is_numeric($record['alder'])) {
                    $data['records'][$index]['alder'] = (string) $record['alder'];
                }
            }
        }

        $validator = Validator::make($data, [
            'records' => 'required|array|min:1|max:1000',
            'records.*.id' => 'nullable|integer|exists:sweden_personer,id',
            'records.*.adress' => 'nullable|string|max:255',
            'records.*.fornamn' => 'nullable|string|max:255',
            'records.*.efternamn' => 'nullable|string|max:255',
            'records.*.personnamn' => 'nullable|string|max:255',
            'records.*.postnummer' => 'nullable|string|max:50',
            'records.*.postort' => 'nullable|string|max:255',
            'records.*.kommun' => 'nullable|string|max:255',
            'records.*.lan' => 'nullable|string|max:255',
            'records.*.kon' => 'nullable|string|max:50',
            'records.*.civilstand' => 'nullable|string|max:255',
            'records.*.alder' => 'nullable|string|max:50',
            'records.*.ratsit_link' => 'nullable|string|max:255',
            'records.*.ratsit_data' => 'nullable',
            'records.*.is_queue' => 'boolean',
            'records.*.is_done' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $record) {
            try {
                $record['is_queue'] = $record['is_queue'] ?? true;

                if (! empty($record['id'])) {
                    $model = SwedenPersoner::find($record['id']);
                    $recordId = $record['id'];
                    unset($record['id']);

                    if ($model) {
                        $model->update($record);
                    } else {
                        $model = SwedenPersoner::create($record);
                    }
                } elseif (! empty($record['personnummer'])) {
                    $model = SwedenPersoner::updateOrCreate(
                        ['personnummer' => $record['personnummer']],
                        $record
                    );
                } else {
                    $model = SwedenPersoner::updateOrCreate(
                        [
                            'adress' => $record['adress'],
                            'fornamn' => $record['fornamn'],
                            'efternamn' => $record['efternamn'],
                        ],
                        $record
                    );
                }

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
                        'fornamn' => $record['fornamn'] ?? null,
                        'efternamn' => $record['efternamn'] ?? null,
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

    // File import: expects CSV or Excel file
    public function importFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);
        $file = $request->file('file');
        $path = $file->storeAs('imports', Str::random(16).'.'.$file->getClientOriginalExtension());
        $fullPath = Storage::path($path);

        // Use our Excel wrapper if available, else fallback to CSV
        if (in_array($file->getClientOriginalExtension(), ['xlsx', 'xls'])) {
            $imported = $this->importExcel($fullPath);
        } else {
            $imported = $this->importCsv($fullPath);
        }
        Storage::delete($path);

        return response()->json(['success' => true, 'created' => $imported]);
    }

    private function importCsv(string $filePath): int
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        $created = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                SwedenPersoner::create($data);
                $created++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return $created;
    }

    private function importExcel(string $filePath): int
    {
        $imported = 0;
        $import = new class
        {
            public $imported = 0;

            public function model(array $row)
            {
                SwedenPersoner::create($row);
                $this->imported++;
            }
        };

        Excel::import($import, $filePath);

        return $import->imported;
    }
}
