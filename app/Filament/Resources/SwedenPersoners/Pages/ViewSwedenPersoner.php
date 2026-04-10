<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Pages;

use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSwedenPersoner extends ViewRecord
{
    protected static string $resource = SwedenPersonerResource::class;

    protected static ?string $title = 'View Person';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
