<?php

namespace App\Filament\Resources\DialerCampaigns\Pages;

use App\Filament\Resources\DialerCampaigns\DialerCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListDialerCampaigns extends ListRecords
{
    protected static string $resource = DialerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function getHeading(): Htmlable|string|null
    {
        return null;
    }
}
