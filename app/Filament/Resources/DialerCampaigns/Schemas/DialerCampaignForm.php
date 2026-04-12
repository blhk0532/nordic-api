<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns\Schemas;

use App\Enums\DialerCampaignStatus;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DialerCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Campaign settings')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('team_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options(collect(DialerCampaignStatus::cases())->mapWithKeys(fn (DialerCampaignStatus $status): array => [
                                $status->value => str($status->value)->replace('_', ' ')->title()->toString(),
                            ])->all())
                            ->default(DialerCampaignStatus::Draft->value)
                            ->required(),
                        TextInput::make('source_channel')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('context')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('caller_id')
                            ->maxLength(255),
                        TextInput::make('max_concurrent_calls')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('max_attempts')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('retry_delay_seconds')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
            ]);
    }
}
