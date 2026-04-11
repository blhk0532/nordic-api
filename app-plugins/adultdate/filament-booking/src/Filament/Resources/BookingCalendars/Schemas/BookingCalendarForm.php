<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Schemas;

use Adultdate\FilamentBooking\Models\Booking\Brand as BookingBrand;
use Adultdate\FilamentBooking\Models\Booking\Service as BookingService;
use Adultdate\FilamentBooking\Models\User;
use App\Models\Admin;
use App\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use WallaceMartinss\FilamentEvolution\Models\WhatsappInstance;

class BookingCalendarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->columns(5)->columnSpanFull()->schema([
                    Grid::make()->columns(1)->columnSpan(3)->schema([
                        Section::make('Main Settings')->schema([
                            TextInput::make('name')
                                ->helperText('Name of the booking calendar')
                                ->required(),
                            Select::make('owner_id')
                                ->relationship('owner', 'name', fn ($query) => $query->where('role', UserRole::SERVICE))
                                ->helperText('The ID of the Tekniker that calendar belongs to')
                                ->required(),
                            TextInput::make('google_calendar_id')
                                ->label('Google Calendar ID')
                                ->helperText('The ID of the Google Calendar to sync with (e.g., your-calendar-id@group.calendar.google.com)')
                                ->placeholder('your-calendar-id@group.calendar.google.com'),

                            Toggle::make('is_active')
                                ->default(true)
                                ->required(),
                        ]),
                        Section::make('Notifications')->schema([
                            TextInput::make('notify_emails')
                                ->label('Notification Emails')
                                ->helperText('Email addresses to notify on new bookings, separated by commas')
                                ->placeholder('Enter comma separated email addresses'),
                            Select::make('whatsapp_id')
                                ->label('WhatsApp Instance')
                                ->helperText('Connected whatsapp instance for sending notifications')
                                ->options(fn () => WhatsappInstance::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (WhatsappInstance $instance) => [
                                        $instance->id => sprintf('%s (%s)', $instance->name, $instance->number),
                                    ]))
                                ->searchable()
                                ->placeholder('Select WhatsApp instance'),
                            Select::make('whatsapp_numbers')
                                ->label('WhatsApp Numbers')
                                ->helperText('WhatsApp instances to receive notifications')
                                ->multiple()
                                ->options(fn () => WhatsappInstance::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (WhatsappInstance $instance) => [
                                        $instance->id => sprintf('%s (%s)', $instance->name, $instance->number),
                                    ]))
                                ->searchable()
                                ->placeholder('Select WhatsApp instances for receiving numbers'),
                            Select::make('notification_user_ids')
                                ->label('Notification Recipients')
                                ->helperText('Users and admins to receive database notifications for new bookings')
                                ->multiple()
                                ->options(collect([
                                    ...User::all()->mapWithKeys(fn ($user) => ["user-{$user->id}" => "User: {$user->name}"]),
                                    ...Admin::all()->mapWithKeys(fn ($admin) => ["admin-{$admin->id}" => "Admin: {$admin->name}"]),
                                ]))
                                ->searchable()
                                ->placeholder('Select users and admins for notifications'),
                        ]),
                    ]),
                    Grid::make()->columns(1)->columnSpan(2)->schema([
                        Section::make('Users with Access')->schema([
                            Select::make('access')
                                ->label('Users with Access')
                                ->helperText('All users who can access this booking calendar')
                                ->multiple()
                                ->options(User::all()->pluck('name', 'id'))
                                ->searchable(),
                        ]),
                        Section::make('Brand & Services')->schema([
                            Select::make('brand_id')
                                ->label('Brand')
                                ->options(BookingBrand::all()->pluck('name', 'id'))
                                ->searchable()
                                ->placeholder('Select a brand'),
                            Select::make('service_ids')
                                ->label('Services')
                                ->multiple()
                                ->options(function ($get) {
                                    $brandId = $get('brand_id');
                                    if ($brandId) {
                                        return BookingService::where('booking_brand_id', $brandId)->pluck('name', 'id');
                                    }

                                    return [];
                                })
                                ->searchable()
                                ->placeholder('Select services for the selected brand'),
                        ]),
                        Section::make('Sharing')->schema([
                            TextInput::make('public_url')
                                ->label('Public URL'),
                            TextInput::make('embed_code')
                                ->label('Embed Code'),
                            TextInput::make('public_address_ical')
                                ->label('Public Address iCal'),
                            TextInput::make('secret_address_ical')
                                ->label('Secret Address iCal'),
                            TextInput::make('shareable_link')
                                ->label('Shareable Link'),
                        ]),
                    ]),
                ]),
            ]);
    }
}
