<?php

namespace App\Filament\Resources\DialerLeads\Pages;

use App\Filament\Resources\DialerLeads\DialerLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDialerLead extends EditRecord
{
    protected static string $resource = DialerLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
