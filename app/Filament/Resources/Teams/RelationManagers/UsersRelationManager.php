<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Models\Team;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Team $ownerRecord
 */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $relatedResource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('users.id', '!=', $this->ownerRecord->owner()?->id))
                    ->preloadRecordSelect()
                    ->multiple(),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
