<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets\Concerns;

trait IsBackwardCompatible
{
    protected function getHeaderActions(): array
    {
        return $this->headerActions();
    }

    protected function getFormActions(): array
    {
        return $this->modalActions();
    }
}
