<?php

namespace App\Filament\Resources\DialerCampaigns\Pages;

use App\Filament\Resources\DialerCampaigns\DialerCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDialerCampaign extends EditRecord
{
    protected static string $resource = DialerCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
