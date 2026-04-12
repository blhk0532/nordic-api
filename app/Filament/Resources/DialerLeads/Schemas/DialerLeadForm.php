<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerLeads\Schemas;

use App\Enums\DialerLeadStatus;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DialerLeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Lead')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('team_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        Select::make('dialer_campaign_id')
                            ->label('Campaign')
                            ->relationship(
                                name: 'campaign',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): void {
                                    $tenant = Filament::getTenant();

                                    $query->when($tenant !== null, fn (Builder $campaignQuery) => $campaignQuery->where('team_id', $tenant->id));
                                },
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
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
                        TextInput::make('attempts_count')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('last_disposition')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
