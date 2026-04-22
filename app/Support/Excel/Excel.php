<?php

declare(strict_types=1);

namespace App\Support\Excel;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{
    public function toArray(mixed $import, string $filePath, ?string $sheet = null): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $sheet ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return [];
        }

        return array_values($rows);
    }

    public function import(object $import, string $filePath, ?string $disk = null): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return;
        }

        $headers = array_shift($rows);

        if (method_exists($import, 'model')) {
            foreach ($rows as $row) {
                $data = array_combine($headers, $row);
                $import->model($data);
            }
        } elseif (method_exists($import, 'collection')) {
            $collection = new Collection();
            foreach ($rows as $row) {
                $data = array_combine($headers, $row);
                $collection->push($data);
            }
            $import->collection($collection);
        }
    }

    public function export(object $export, string $filePath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (method_exists($export, 'collection')) {
            $collection = $export->collection();

            if (! $collection instanceof Collection) {
                $collection = collect($collection);
            }

            $headers = [];
            if ($collection->isNotEmpty()) {
                $first = $collection->first();
                if (is_array($first)) {
                    $headers = array_keys($first);
                }
            }

            if (method_exists($export, 'headings')) {
                $headings = $export->headings();
                if (is_array($headings)) {
                    $headers = $headings;
                }
            }

            if (! empty($headers)) {
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col.'1', $header);
                    $col++;
                }
            }

            $rowNum = 2;
            foreach ($collection as $item) {
                $col = 'A';
                foreach ($headers as $header) {
                    $value = is_array($item) ? ($item[$header] ?? null) : ($item->{$header} ?? null);
                    $sheet->setCellValue($col.$rowNum, $value);
                    $col++;
                }
                $rowNum++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    public static function __callStatic(string $method, array $args)
    {
        return (new self())->{$method}(...$args);
    }

    public function __call(string $method, array $args)
    {
        return match ($method) {
            'toArray' => $this->toArray($args[0] ?? null, $args[1] ?? '', $args[2] ?? null),
            'import' => $this->import($args[0], $args[1] ?? '', $args[2] ?? null),
            'export' => $this->export($args[0], $args[1] ?? ''),
            default => null,
        };
    }
}