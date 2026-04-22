<?php

namespace App\Filament\Resources\AudioVoiceFlows\Pages;

use App\Filament\Resources\AudioVoiceFlows\AudioVoiceFlowResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAudioVoiceFlow extends EditRecord
{
    protected static string $resource = AudioVoiceFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
