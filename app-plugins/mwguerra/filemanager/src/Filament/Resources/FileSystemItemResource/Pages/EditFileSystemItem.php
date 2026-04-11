<?php

namespace MWGuerra\FileManager\Filament\Resources\FileSystemItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MWGuerra\FileManager\Filament\Resources\FileSystemItemResource;

class EditFileSystemItem extends EditRecord
{
    protected static string $resource = FileSystemItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
