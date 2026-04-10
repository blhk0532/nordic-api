<?php

declare(strict_types=1);

namespace App\Filament\Resources\SwedenPersoners\Pages;

use App\Filament\Resources\SwedenPersoners\SwedenPersonerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSwedenPersoner extends EditRecord
{
    protected static string $resource = SwedenPersonerResource::class;

    protected static ?string $title = 'Edit Person';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
