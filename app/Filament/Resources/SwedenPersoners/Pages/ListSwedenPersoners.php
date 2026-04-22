<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Pages;

use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use Filament\AdvancedExport\Traits\HasAdvancedExport;
use Filament\AdvancedExport\Jobs\ProcessExportJob;
use Filament\AdvancedExport\Support\ExportConfig;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListSwedenPersoners extends ListRecords
{
    use HasAdvancedExport;

    protected static string $resource = SwedenPersonerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getAdvancedExportHeaderAction()
                ->action(function (array $data): ?\Symfony\Component\HttpFoundation\BinaryFileResponse {
                    // Check if we should queue this or process immediately
                    $recordCount = $this->getExportRecordCount();
                    $threshold = config('advanced-export.limits.queue_threshold', 2000);

                    if ($recordCount > $threshold) {
                        $activeFilters = $this->extractActiveFilters();
                        $fileName = $this->generateFileName('advanced', $data['export_format'] ?? 'xlsx');
                        
                        ProcessExportJob::dispatch(
                            $modelClass = static::$resource::getModel(),
                            $filters = $activeFilters,
                            $fileName = $fileName,
                            $viewName = $this->getAdvancedExportViewName(),
                            $columnsConfig = $data['columns'] ?? [],
                            $orderColumn = $data['order_column'] ?? 'created_at',
                            $orderDirection = $data['order_direction'] ?? 'desc',
                            $relationships = $this->getExportRelationships(),
                            $userId = auth()->id()
                        );

                        $this->showQueuedNotification();

                        return null;
                    }

                    // Set higher memory and execution time for the synchronous part
                    ini_set('memory_limit', '1024M');
                    set_time_limit(300);

                    return $this->exportWithCustomColumns(
                        $data['columns'] ?? [],
                        $data['order_column'] ?? 'created_at',
                        $data['order_direction'] ?? 'desc',
                        $data['export_format'] ?? 'xlsx'
                    );
                }),
        ];
    }

    protected function getExportLimit(): int
    {
        return 100000;
    }

    protected function getExportQuery(array $activeFilters): Builder
    {
        // Get the current selected records from the table
        $selectedRecords = $this->getSelectedTableRecords();

        $query = static::$resource::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with($this->getExportRelationships());

        // If records are selected in the UI, only export those
        if ($selectedRecords && $selectedRecords->isNotEmpty()) {
            $query->whereIn(
                $query->getModel()->getQualifiedKeyName(),
                $selectedRecords->modelKeys()
            );
        }

        return $query;
    }

    protected function applyCustomFilter(Builder $query, string $filterName, mixed $filterValue): void
    {
        // Handle specific logic for filters that use custom queries in the Table class
        if ($filterName === 'telefon') {
            $value = is_array($filterValue) ? ($filterValue['value'] ?? null) : $filterValue;
            if ($value === 'yes') {
                $query->whereNotNull('telefon');
            } elseif ($value === 'no') {
                $query->whereNull('telefon');
            }

            return;
        }

        if ($filterName === 'is_hus') {
            $value = is_array($filterValue) ? ($filterValue['value'] ?? null) : $filterValue;
            if ($value === 'yes') {
                $query->where('is_hus', true);
            } elseif ($value === 'no') {
                $query->where('is_hus', false);
            }

            return;
        }

        foreach (['ratsit_data', 'hitta_data', 'merinfo_data', 'eniro_data', 'upplysning_data', 'mrkoll_data'] as $customFilter) {
            if ($filterName === $customFilter) {
                $value = is_array($filterValue) ? ($filterValue['value'] ?? null) : $filterValue;
                if ($value === 'yes') {
                    $query->whereNotNull($customFilter);
                } elseif ($value === 'no') {
                    $query->whereNull($customFilter);
                }

                return;
            }
        }

        // Apply generic filters automatically for others
        $this->applyGenericFilter($query, $filterName, $filterValue);
    }

    protected function buildExportQuery(array $activeFilters): Builder
    {
        $query = $this->getExportQuery($activeFilters);

        // Only apply filters if no specific records are selected manually
        $selectedRecords = $this->getSelectedTableRecords();
        if (! $selectedRecords || $selectedRecords->isEmpty()) {
            $this->applyFiltersToQuery($query, $activeFilters);
        }

        return $query;
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
