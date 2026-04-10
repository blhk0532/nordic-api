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
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Facades\Excel;

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

    // File import: expects CSV or Excel file
    public function importFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);
        $file = $request->file('file');
        $path = $file->storeAs('imports', Str::random(16).'.'.$file->getClientOriginalExtension());
        $fullPath = Storage::path($path);

        // Use Laravel Excel if available, else fallback to CSV
        if (in_array($file->getClientOriginalExtension(), ['xlsx', 'xls']) && class_exists('Maatwebsite\\Excel\\Facades\\Excel')) {
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
        // Requires maatwebsite/excel
        $imported = 0;
        Excel::import(new class implements ToModel
        {
            public $imported = 0;

            public function model(array $row)
            {
                SwedenPersoner::create($row);
                $this->imported++;
            }
        }, $filePath);

        return $imported;
    }
}
