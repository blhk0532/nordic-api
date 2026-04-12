<?php

namespace App\Filament\Resources\DialerCampaigns\Pages;

use App\Filament\Resources\DialerCampaigns\DialerCampaignResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDialerCampaign extends ViewRecord
{
    protected static string $resource = DialerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
