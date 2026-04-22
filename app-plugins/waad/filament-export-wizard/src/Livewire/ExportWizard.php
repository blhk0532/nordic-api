<?php

namespace Waad\FilamentExportWizard\Livewire;

use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ExportWizard extends Component
{
    public int $step = 1;

    public string $modelClass = '';

    public array $selectedColumns = [];

    public array $columnOptions = [];

    public string $format = 'csv';

    public function mount(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->columnOptions = $this->getColumnOptions();
    }

    public function getColumnOptions(): array
    {
        if (! $this->modelClass || ! class_exists($this->modelClass)) {
            return [];
        }

        $model = new $this->modelClass;
        $table = $model->getTable();

        try {
            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function nextStep()
    {
        $this->step++;
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function export()
    {
        // Implementation for exporting logic
        $this->dispatch('notify', [
            'status' => 'success',
            'message' => 'Export started!',
        ]);

        $this->dispatch('close-modal', id: 'export-wizard');
    }

    public function render()
    {
        return view('filament-export-wizard::wizard');
    }
}
