<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Enums\TeamRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\Team;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
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
            ->columns([
                TextColumn::make('name')
                    ->label('User')
                    ->formatStateUsing(function ($state, $record) {
                        $avatarUrl = $record->email ? 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($record->email))).'?d=mp&s=64' : null;
                        $name = $record->name ?? '';
                        $email = $record->email ?? '';

                        return view('filament.columns.user-avatar', [
                            'avatarUrl' => $avatarUrl,
                            'name' => $name,
                            'email' => $email,
                        ])->render();
                    })
                    ->html(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                SelectColumn::make('pivot.role')
                    ->label('Team Role')
                    ->options(collect(TeamRole::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray())
                    ->disabled(fn ($record) => $record->pivot->role === TeamRole::Owner),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('users.id', '!=', $this->ownerRecord->owner()?->id))
                    ->preloadRecordSelect()
                    ->multiple()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->label('Team Role')
                            ->options(collect(TeamRole::assignable())->pluck('label', 'value')->toArray())
                            ->default(TeamRole::Member->value),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
