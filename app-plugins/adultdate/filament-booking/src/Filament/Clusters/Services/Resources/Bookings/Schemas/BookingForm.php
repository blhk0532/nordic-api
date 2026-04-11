<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Schemas;

use Adultdate\FilamentBooking\Enums\BookingStatus;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\Client;
use Adultdate\FilamentBooking\Models\Booking\Service;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema(self::getDetailsComponents())
                            ->columns(2),
                        Section::make('Tjänst')
                            ->schema([
                                self::getItemsRepeater(),
                            ]),
                        Section::make()
                            ->schema(self::getDetailsComponents2())
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 3]),

                // Removed created_at / updated_at display section — not needed in modal
            ])
            ->columns(3);
    }

    /**
     * Determine if the current user may see and edit the booking `status` field.
     */
    public static function canShowStatus(?Booking $record): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (is_object($user) && method_exists($user, 'hasRole')) {
            if (call_user_func([$user, 'hasRole'], 'admin') ||
                call_user_func([$user, 'hasRole'], 'super') ||
                call_user_func([$user, 'hasRole'], 'manager')) {
                return true;
            }
        }

        if (in_array($user->role ?? null, ['admin', 'super', 'manager'])) {
            return true;
        }

        return false;
    }

    /** @return array<Component> */
    public static function getClientComponents(): array
    {
        return [

        ];
    }

    /** @return array<Component> */
    public static function getDetailsComponents(array $clientDefaults = []): array
    {
        return [
            TextInput::make('number')
                ->default('OR-'.random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->hidden()
                ->maxLength(32)
                ->unique(Booking::class, 'number', ignoreRecord: true),
            Select::make('service_id')
                ->relationship('service', 'name')
                ->searchable()
                ->hidden(),
            Select::make('service_user_id')
                ->label('Service User')
                ->options(User::where('role', 'service')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            DatePicker::make('service_date')
                ->label('Datum')
                ->required()
                ->columnSpan(1),

            Group::make()
                ->schema([
                    TimePicker::make('start_time')
                        ->label('Starttid')
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->native(false)
                        ->required(),

                    TimePicker::make('end_time')
                        ->label('Sluttid')
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->native(false)
                        ->required(),
                ])
                ->columns(2)
                ->columnSpan(1),

            Select::make('booking_client_id')
                ->relationship('client', 'name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    Group::make()
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->default($clientDefaults['name'] ?? null)
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->default($clientDefaults['phone'] ?? null)
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('email')
                                ->label('Email address')
                                ->default($clientDefaults['email'] ?? null)
                                ->email()
                                ->maxLength(255)
                                ->unique(),

                            TextInput::make('street')
                                ->label('Street address')
                                ->default($clientDefaults['street'] ?? null)
                                ->maxLength(255)
                                ->required(),

                            TextInput::make('zip')
                                ->label('Postal code')
                                ->default($clientDefaults['zip'] ?? null)
                                ->maxLength(20)
                                ->required(),

                            TextInput::make('city')
                                ->default($clientDefaults['city'] ?? null)
                                ->maxLength(255)
                                ->required(),

                            TextInput::make('country')
                                ->hidden()
                                ->placeholder('Sweden'),
                        ]),
                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading('Create client')
                        ->modalSubmitActionLabel('Create client')
                        ->modalWidth('lg');
                })
                ->createOptionUsing(function (array $data) {
                    $country = $data['country'] ?? null;
                    if (array_key_exists('country', $data)) {
                        unset($data['country']);
                    }

                    $client = Client::create($data);

                    if ($country) {
                        $client->update(['address' => $country]);
                    }

                    return $client->id;
                }),

            TextInput::make('booking_user_id')
                ->hidden()
                ->dehydrated(),

            TextInput::make('admin_id')
                ->hidden()
                ->dehydrated(),

        ];
    }

    /** @return array<Component> */
    public static function getDetailsComponents2(array $clientDefaults = []): array
    {
        return [
            ToggleButtons::make('status')
                ->options(BookingStatus::restrictedOptions())

                ->inline()
                ->required()
                ->hidden(fn (?Booking $record) => ! self::canShowStatus($record))
                ->columnSpan('full'),
            RichEditor::make('notes')
                ->label('Anteckningar')
                ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->label('Tjänst')
            ->relationship()
            ->schema([
                Select::make('booking_service_id')
                    ->label('Tjänst')
                    ->options(Service::query()->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('unit_price', Service::find($state)?->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->searchable()
                    ->columnSpan(2),

                TextInput::make('qty')
                    ->label('Antal')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('unit_price')
                    ->label('Pris')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan(1),
            ])
            ->columns(4)
            ->orderColumn('sort')
            ->defaultItems(1)
            ->hiddenLabel();
    }
}
