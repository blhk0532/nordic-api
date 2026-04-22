<?php

namespace Waad\FilamentExportWizard\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\View;

class ExportWizardAction extends Action
{
    protected ?string $modelClass = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);
        $this->label(__('Export Wizard'));
        $this->modalHeading(__('Export Wizard'));
        $this->modalWidth(Width::Full);
        $this->closeModalByClickingAway(false);
        $this->modalContent(fn () => View::make('filament-export-wizard::wizard-modal', [
            'modelClass' => $this->getModelClass(),
        ]));
    }

    public static function make(?string $name = 'exportWizard'): static
    {
        return parent::make($name);
    }

    public function forModel(string $model): static
    {
        $this->modelClass = $model;

        return $this;
    }

    public function getModelClass(): ?string
    {
        return $this->modelClass ?? $this->getModel();
    }
}
