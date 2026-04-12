<?php

namespace App\Filament\Resources\DialerCampaigns\Pages;

use App\Filament\Resources\DialerCampaigns\DialerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDialerCampaigns extends ListRecords
{
    protected static string $resource = DialerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // CreateAction::make(),
        ];
    }

            public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return null;
    }
}
