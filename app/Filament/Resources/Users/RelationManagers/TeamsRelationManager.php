<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Teams\TeamResource;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property User $ownerRecord
 */
class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    protected static ?string $relatedResource = TeamResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('teams.user_id', '!=', $this->ownerRecord->id))
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
