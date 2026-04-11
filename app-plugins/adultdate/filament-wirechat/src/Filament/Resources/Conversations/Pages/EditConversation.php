<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Conversations\Pages;

use AdultDate\FilamentWirechat\Filament\Resources\Conversations\ConversationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConversation extends EditRecord
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
