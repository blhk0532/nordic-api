<?php

namespace App\Filament\Resources\DialerLeads\Pages;

use App\Filament\Resources\DialerLeads\DialerLeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDialerLead extends ViewRecord
{
    protected static string $resource = DialerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
