<?php

namespace App\Filament\Resources\DialerLeads\Pages;

use App\Filament\Resources\DialerLeads\DialerLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDialerLeads extends ListRecords
{
    protected static string $resource = DialerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  CreateAction::make(),
        ];
    }

            public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return null;
    }
}
