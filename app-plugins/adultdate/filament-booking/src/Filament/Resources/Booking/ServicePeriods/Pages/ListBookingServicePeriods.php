<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages;

use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\BookingServicePeriodResource;
use Adultdate\FilamentBooking\Filament\Widgets\BookingCalendarWidget;
use Adultdate\FilamentBooking\Models\Booking\BookingLocation;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\Service;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class ListBookingServicePeriods extends ListRecords
{
    protected static ?int $sort = -1;

    protected static ?int $navigationSort = -3;

    protected static string $resource = BookingServicePeriodResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            BookingCalendarWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->slideOver()
                ->modalWidth('xl')
                ->schema([

                    Select::make('service_id')
                        ->label('Service')
                        ->options(Service::pluck('name', 'id'))
                        ->searchable()
                        ->hidden()
                        ->preload()
                        ->required(),
                    FusedGroup::make([

                        Select::make('service_user_id')
                            ->placeholder('Service Technician')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Select::make('booking_client_id')
                            ->label('Client')
                            ->options(Client::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Customer / Client')
                            ->createOptionForm([
                                Section::make('')
                                    ->inlineLabel()
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),

                                        TextInput::make('address')
                                            ->maxLength(255),
                                        TextInput::make('city')
                                            ->maxLength(255),
                                        TextInput::make('postal_code')
                                            ->maxLength(20),
                                        TextInput::make('country')
                                            ->default('Sweden')
                                            ->dehydrated(false)
                                            ->hidden(),
                                    ]),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $data['country'] = 'Sweden';
                                $client = Client::create($data);

                                return $client->id;
                            })
                            ->required(),
                    ])
                        ->label('Location')
                        ->columns(2),
                    Select::make('booking_location_id')
                        ->label('Location')
                        ->options(BookingLocation::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->hidden()
                        ->required(),

                    FusedGroup::make([

                        DatePicker::make('service_date')
                            ->label('Service Date')
                            ->required()
                            ->native(false),

                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds(false)
                            ->native(false),

                    ])
                        ->label('Service Date - Start ⏰ Time')
                        ->columns(3),

                    Select::make('status')->hidden(fn () => ! in_array(Auth::user()?->role, ['admin', 'super', 'manager']))
                        ->label('Status')
                        ->options(BookingStatus::restrictedOptions())
                        ->default(BookingStatus::Booked->value)
                        ->hidden()
                        ->required(),

                    TextInput::make('total_price')
                        ->label('Total Price')
                        ->numeric()

                        ->hidden()
                        ->prefix('SEK'),

                    Textarea::make('service_note')
                        ->label('Service Note')
                        ->hidden()
                        ->rows(3),

                    Repeater::make('items')
                        ->label('Booking Items')
                        ->schema([
                            Select::make('booking_service_id')
                                ->label('Service')
                                ->options(Service::pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            TextInput::make('qty')
                                ->label('Quantity')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),

                            TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->numeric()
                                ->prefix('SEK')
                                ->default(0)
                                ->required(),
                        ]),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3),
                ])
                ->action(function (array $data): void {}),

        ];
    }

    protected function getFormSchema(): array
    {
        return [];
    }
}
