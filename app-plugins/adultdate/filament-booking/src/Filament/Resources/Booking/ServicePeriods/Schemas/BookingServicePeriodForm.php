<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class BookingServicePeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('service_date')
                    ->required(),
                Select::make('service_user_id')
                    ->relationship('serviceUser', 'name')
                    ->required(),
                TextInput::make('service_location'),
                TimePicker::make('start_time'),
                TimePicker::make('end_time'),
                TextInput::make('period_type')
                    ->default('unavailable'),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
