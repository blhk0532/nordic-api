<?php

namespace App\Filament\Resources\AudioVoiceFlows\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AudioVoiceFlowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Voice Script')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->description),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'approved',
                        'success' => 'active',
                        'danger' => 'archived',
                    ])
                    ->sortable(),

                TextColumn::make('priority')
                    ->label('Queue Priority')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? sprintf('%d sec', $state) : '—')
                    ->sortable(),

                TextColumn::make('play_count')
                    ->label('Plays')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('tags')
                    ->label('Tags')
                    ->formatStateUsing(fn ($state) => $state ? implode(', ', $state) : '—')
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ]),

                Filter::make('active_only')
                    ->toggle()
                    ->label('Active Only')
                    ->query(fn (Builder $query) => $query->where('status', 'active')),

                TrashedFilter::make(),
            ])
            ->defaultSort('priority', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
