<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Wezlo\FilamentGridList\Concerns\HasGridList;
use Wezlo\FilamentGridList\GridListConfiguration;

class ListTeams extends ListRecords
{
    use HasGridList;

    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //  CreateAction::make(),
        ];
    }

    public function getHeading(): Htmlable|string|null
    {
        return null;
    }

    public function gridList(GridListConfiguration $config): GridListConfiguration
    {
        return $config
            ->gridColumns(['default' => 1, 'sm' => 2, 'lg' => 4])
            ->header(fn ($record) => $record->name)
            ->content(fn ($record) => Str::limit($record->slut, 100))
            ->footer(fn ($record) => 'Personal = '.($record->is_personal ? 'Yes' : 'No'));
    }
}
