<?php

namespace App\Filament\Resources\Merinfos\Pages;

use App\Filament\Resources\Merinfos\MerinfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListMerinfos extends ListRecords
{
    protected static string $resource = MerinfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //   Actions\CreateAction::make(),
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
