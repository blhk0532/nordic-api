<?php

namespace App\Filament\Resources\DialerLeads\Pages;

use App\Filament\Resources\DialerLeads\DialerLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListDialerLeads extends ListRecords
{
    protected static string $resource = DialerLeadResource::class;

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
}
