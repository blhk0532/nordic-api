<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Tables;

use Adultdate\FilamentBooking\Enums\BookingState;
use Adultdate\FilamentBooking\Enums\Failed;
use Adultdate\FilamentBooking\Enums\Paid;
use Adultdate\FilamentBooking\Enums\Pending;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Guava\FilamentIconSelectColumn\Tables\Columns\IconSelectColumn;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('currency')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->money(),
                    ]),
                IconSelectColumn::make('state')
                    ->options(BookingState::toOptions())
                    ->icons([
                        Pending::class => 'heroicon-o-clock',
                        Paid::class => 'heroicon-o-check-circle',
                        Failed::class => 'heroicon-o-x-circle',
                    ]),
                TextColumn::make('created_at')
                    ->label('Booking date')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                TrashedFilter::make(),

                Filter::make('created_at')
                    ->label('Booking date')
                    ->schema([
                        // keep simple - use Filament datepickers if desired
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        return [];
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function (): void {
                        Notification::make()
                            ->title('zzz')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Group::make('created_at')
                    ->label('Booking date')
                    ->date()
                    ->collapsible(),
            ]);
    }
}
