<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns\RelationManagers;

use App\Enums\DialerLeadStatus;
use App\Filament\Resources\DialerLeads\DialerLeadResource;
use App\Models\DialerCampaign;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * @property DialerCampaign $ownerRecord
 */
class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';

    protected static ?string $relatedResource = DialerLeadResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                TextColumn::make('attempts_count')
                    ->label('Attempts'),
                TextColumn::make('last_disposition')
                    ->formatStateUsing(fn (?string $state): string => $state ? str($state)->replace('_', ' ')->title()->toString() : '—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'team_id' => $this->ownerRecord->team_id,
                        'status' => $data['status'] ?? DialerLeadStatus::Pending->value,
                    ])
                    ->form([
                        TextInput::make('name')
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options(collect(DialerLeadStatus::cases())->mapWithKeys(fn (DialerLeadStatus $status): array => [
                                $status->value => str($status->value)->replace('_', ' ')->title()->toString(),
                            ])->all())
                            ->default(DialerLeadStatus::Pending->value)
                            ->required(),
                        TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
