<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingServicePeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('service_date')
                    ->date(),
                TextEntry::make('serviceUser.name')
                    ->label('Service user'),
                TextEntry::make('service_location')
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('end_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('period_type')
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
