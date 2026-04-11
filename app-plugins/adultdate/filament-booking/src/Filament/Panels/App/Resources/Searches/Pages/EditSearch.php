<?php

declare(strict_types=1);

namespace App\Filament\Panels\App\Resources\Searches\Pages;

use App\Filament\Panels\App\Resources\Searches\SearchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSearch extends EditRecord
{
    protected static string $resource = SearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
