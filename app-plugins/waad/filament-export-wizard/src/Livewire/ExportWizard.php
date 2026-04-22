<?php

namespace Waad\FilamentExportWizard\Livewire;

use App\Exports\ModelExporter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;
use Livewire\Component;

class ExportWizard extends Component implements HasForms, HasSchemas
{
    use InteractsWithForms;

    public string $modelClass = '';

    public array $data = [];

    public function mount(string $modelClass)
    {
        $this->modelClass = $modelClass;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Selection')
                        ->description('Select columns and map data')
                        ->schema([
                            CheckboxList::make('columns')
                                ->options($this->getColumnOptions())
                                ->columns(3)
                                ->required(),
                        ]),
                    Step::make('Format')
                        ->description('Choose your export format')
                        ->schema([
                            Radio::make('format')
                                ->options([
                                    'csv' => 'CSV (Comma Separated Values)',
                                    'xlsx' => 'Excel (XLSX)',
                                ])
                                ->default('csv')
                                ->required(),
                        ]),
                ])
                    ->persistStepInQueryString('export_wizard_step')
                    ->submitAction(view('filament-export-wizard::submit-button')),
            ])
            ->statePath('data');
    }

    public function getColumnOptions(): array
    {
        if (! $this->modelClass || ! class_exists($this->modelClass)) {
            return [];
        }

        $model = new $this->modelClass;
        $table = $model->getTable();
        $columns = DbSchema::getColumnListing($table);

        $options = [];
        foreach ($columns as $column) {
            $options[$column] = str($column)->headline()->toString();

            // Auto-detect JSON columns ending in _data
            if (str_ends_with($column, '_data')) {
                // In a real scenario, we might want to sample a record to see keys
                // For now, we'll just allow the column itself
            }
        }

        return $options;
    }

    public function export()
    {
        $data = $this->form->getState();

        $exporter = new ModelExporter(
            $this->modelClass,
            $data['columns'],
            $data['format']
        );

        $filename = $exporter->export(($this->modelClass)::query());

        Notification::make()
            ->title('Export Started')
            ->body('Your file is being generated: '.$filename)
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'export-wizard');

        return response()->download(storage_path('app/public/'.$filename));
    }

    public function render()
    {
        return view('filament-export-wizard::wizard');
    }
}
