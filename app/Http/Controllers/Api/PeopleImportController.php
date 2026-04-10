<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeopleImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        if ($request->hasFile('file')) {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt,json',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            $results = match ($extension) {
                'csv' => $this->processCsv($file),
                'json' => $this->processJson($file),
                default => ['success' => 0, 'failed' => 0, 'errors' => ['Unsupported file format']],
            };
        } else {
            $content = $request->getContent();
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON'], 400);
            }

            $results = $this->processData($decoded);
        }

        return response()->json($results);
    }

    private function processCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $success = 0;
        $failed = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                $this->createOrUpdatePerson($data);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        fclose($handle);

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    private function processJson($file): array
    {
        $content = json_decode(file_get_contents($file->getRealPath()), true);

        if (! is_array($content)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ['Invalid JSON format']];
        }

        return $this->processData($content);
    }

    private function processData(array $data): array
    {
        $items = array_is_list($data) ? $data : [$data];

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                $this->createOrUpdatePerson($item);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed, 'errors' => $errors];
    }

    private function createOrUpdatePerson(array $data): Person
    {
        $normalized = [
            'name' => $data['name'] ?? $data['Name'] ?? null,
            'street' => $data['street'] ?? $data['adress'] ?? $data['address'] ?? $data['Address'] ?? null,
            'zip' => $data['zip'] ?? $data['Zip'] ?? null,
            'city' => $data['city'] ?? $data['City'] ?? null,
            'kommun' => $data['kommun'] ?? $data['Kommun'] ?? null,
            'phone' => $data['phone'] ?? $data['Phone'] ?? null,
        ];

        $normalized = array_map(fn ($v) => $v === '' ? null : $v, $normalized);

        $existing = Person::where('name', $normalized['name'])
            ->where('street', $normalized['street'])
            ->where('zip', $normalized['zip'])
            ->where('city', $normalized['city'])
            ->first();

        if ($existing) {
            $existing->update(array_filter($normalized));

            return $existing;
        }

        return Person::create(array_filter($normalized));
    }
}
