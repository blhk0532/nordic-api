<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\AuthRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Waad\FilamentExportWizard\Actions\ExportWizardAction;

class UsersTable
{
    public static function configure(Table $table): Table
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
                SelectColumn::make('role')
                    ->options(collect(AuthRole::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray())
                    ->disabled(fn ($record) => $record->role instanceof AuthRole ? $record->role === AuthRole::Super : $record->role === 'super'),
                // IconColumn::make('status')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-check-badge')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->sortable(),
                // BadgeableColumn::make('name')
                //     ->hidden()
                //     ->separator(':'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                // TextColumn::make('company.name')
                //     ->label('Company')
                //     ->badge()
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(),
                TextColumn::make('currentTeam.name')
                    ->label('Current Team')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('teams.name')
                    ->hidden()
                    ->label('Team Name')
                    ->badge()
                    ->searchable()
                    ->toggleable(),
                //       RatingColumn::make('rating'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->toolbarActions([
                ExportWizardAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => (Filament::auth()->user()->role instanceof AuthRole
                            ? Filament::auth()->user()->role === AuthRole::Super
                            : Filament::auth()->user()->role === 'super')
                        || ($record->role instanceof AuthRole
                            ? $record->role !== AuthRole::Super
                            : $record->role !== 'super')
                    ),
                // Impersonate::make()
                //     ->color('success')
                //     ->label('Login')
                //     ->visible(fn () => auth()->user()->role === 'super'),
                DeleteAction::make()
                    ->visible(fn () => Filament::auth()->user()->role instanceof AuthRole
                            ? Filament::auth()->user()->role === AuthRole::Super
                            : Filament::auth()->user()->role === 'super'
                    ),
            ]);
    }
}
