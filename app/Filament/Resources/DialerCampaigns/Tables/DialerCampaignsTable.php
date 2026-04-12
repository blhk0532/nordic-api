<?php

declare(strict_types=1);

namespace App\Filament\Resources\DialerCampaigns\Tables;

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerCampaignStatus;
use App\Enums\DialerLeadStatus;
use App\Models\DialerCampaign;
use App\Services\DialerCampaignService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DialerCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => str((string) $state)->replace('_', ' ')->title()->toString()),
                TextColumn::make('source_channel')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('context')
                    ->toggleable(),
                TextColumn::make('leads_count')
                    ->counts('leads')
                    ->label('Leads'),
                TextColumn::make('pending_leads_count')
                    ->label('Pending')
                    ->state(fn (DialerCampaign $record): int => $record->leads()->where('status', DialerLeadStatus::Pending->value)->count()),
                TextColumn::make('active_attempts_count')
                    ->label('Active')
                    ->state(fn (DialerCampaign $record): int => $record->attempts()->whereIn('status', [
                        DialerAttemptStatus::Queued->value,
                        DialerAttemptStatus::Sent->value,
                        DialerAttemptStatus::Ringing->value,
                        DialerAttemptStatus::Answered->value,
                    ])->count()),
                TextColumn::make('retry_delay_seconds')
                    ->suffix(' sec')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(DialerCampaignStatus::cases())->mapWithKeys(fn (DialerCampaignStatus $status): array => [
                        $status->value => str($status->value)->replace('_', ' ')->title()->toString(),
                    ])->all()),
            ])
            ->recordActions([
                Action::make('start')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (DialerCampaign $record): bool => in_array($record->status, [DialerCampaignStatus::Draft, DialerCampaignStatus::Paused], true))
                    ->action(function (DialerCampaign $record, DialerCampaignService $service): void {
                        $queued = $service->startCampaign($record);

                        Notification::make()->title("Campaign started ({$queued} queued)")->success()->send();
                    }),
                Action::make('pause')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (DialerCampaign $record): bool => $record->status === DialerCampaignStatus::Running)
                    ->action(function (DialerCampaign $record, DialerCampaignService $service): void {
                        $service->pauseCampaign($record);

                        Notification::make()->title('Campaign paused')->warning()->send();
                    }),
                Action::make('queue_now')
                    ->label('Queue')
                    ->icon('heroicon-o-bolt')
                    ->visible(fn (DialerCampaign $record): bool => $record->status === DialerCampaignStatus::Running)
                    ->action(function (DialerCampaign $record, DialerCampaignService $service): void {
                        $queued = $service->queueNextBatch($record);

                        Notification::make()->title("Queued {$queued} leads")->success()->send();
                    }),
                Action::make('stop')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->visible(fn (DialerCampaign $record): bool => in_array($record->status, [DialerCampaignStatus::Running, DialerCampaignStatus::Paused], true))
                    ->action(function (DialerCampaign $record, DialerCampaignService $service): void {
                        $service->stopCampaign($record);

                        Notification::make()->title('Campaign stopped')->danger()->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
