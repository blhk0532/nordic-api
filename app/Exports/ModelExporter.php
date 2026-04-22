<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class ModelExporter
{
    public function __construct(
        protected string $modelClass,
        protected array $columns = [],
        protected string $format = 'csv'
    ) {}

    public function export(Builder $query): string
    {
        $filename = 'exports/'.strtolower(class_basename($this->modelClass)).'-'.now()->timestamp.'.'.$this->format;

        if ($this->format === 'csv') {
            return $this->exportCsv($query, $filename);
        }

        // Add XLSX support here if needed via Laravel Excel
        return $filename;
    }

    protected function exportCsv(Builder $query, string $filename): string
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);

        // Header
        $csv->insertOne(array_map(fn ($c) => str($c)->headline()->toString(), $this->columns));

        $query->chunk(1000, function (Collection $records) use ($csv) {
            foreach ($records as $record) {
                $row = [];
                foreach ($this->columns as $column) {
                    $row[] = $this->extractValue($record, $column);
                }
                $csv->insertOne($row);
            }
        });

        Storage::disk('public')->put($filename, $csv->toString());

        return $filename;
    }

    protected function extractValue($record, string $column): mixed
    {
        // Logic for extracting data from _data columns (e.g., hitta_data.some_key)
        if (str_contains($column, '.')) {
            [$main, $key] = explode('.', $column, 2);
            $data = $record->{$main};

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            return data_get($data, $key);
        }

        return $record->{$column};
    }
}
