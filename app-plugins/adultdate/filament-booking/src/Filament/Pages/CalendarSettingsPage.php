<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Pages;

use Adultdate\FilamentBooking\Models\CalendarSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CalendarSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'adultdate/filament-booking::pages.calendar-settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|UnitEnum|null $navigationGroup = 'Hantera Kalendrar';

    protected static ?int $sort = 12;

    protected static ?int $navigationSort = 12;

    public function mount(): void
    {
        $settings = CalendarSettings::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'opening_hour_start' => '07:00',
                'opening_hour_end' => '20:00',
                'calendar_timezone' => 'Europe/Stockholm',
            ]
        );

        $this->form->fill([
            'opening_hour_start' => $settings->opening_hour_start?->format('H:i') ?? '07:00',
            'opening_hour_end' => $settings->opening_hour_end?->format('H:i') ?? '20:00',
            'confirmation_sms' => $settings->confirmation_sms,
            'confirmation_email' => $settings->confirmation_email,
            'calendar_weekends' => $settings->calendar_weekends,
            'calendar_theme' => $settings->calendar_theme?->value ?? 'standard',
            'confirmation_sms_number' => $settings->confirmation_sms_number,
            'confirmation_email_address' => $settings->confirmation_email_address,
            'telavox_jwt' => $settings->telavox_jwt,
            'calendar_timezone' => $settings->calendar_timezone ?? 'Europe/Stockholm',
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        CalendarSettings::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'opening_hour_start' => $data['opening_hour_start'],
                'opening_hour_end' => $data['opening_hour_end'],
                'confirmation_sms' => $data['confirmation_sms'],
                'confirmation_email' => $data['confirmation_email'],
                'calendar_weekends' => $data['calendar_weekends'],
                'calendar_theme' => $data['calendar_theme'],
                'confirmation_sms_number' => $data['confirmation_sms_number'],
                'confirmation_email_address' => $data['confirmation_email_address'],
                'telavox_jwt' => $data['telavox_jwt'],
                'calendar_timezone' => $data['calendar_timezone'],
            ]
        );

        Notification::make()
            ->title('Inställningar sparade')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->columns(2)
                ->schema([
                    TimePicker::make('opening_hour_start')
                        ->label('Öppettid Start')
                        ->required()
                        ->native(false)
                        ->seconds(false)
                        ->default('07:00'),

                    TimePicker::make('opening_hour_end')
                        ->label('Öppettid Slut')
                        ->required()
                        ->native(false)
                        ->seconds(false)
                        ->default('20:00')
                        ->rule('after:opening_hour_start'),

                    Textarea::make('confirmation_sms')
                        ->label('Confirmation SMS')
                        ->placeholder('Enter SMS confirmation message'),

                    Textarea::make('confirmation_email')
                        ->label('Confirmation Email')
                        ->placeholder('Enter email confirmation message'),

                    Select::make('calendar_theme')
                        ->label('Calendar Theme')
                        ->options([
                            'standard' => 'Standard',
                            'bootstrap' => 'Bootstrap',
                            'bootstrap5' => 'Bootstrap5',
                        ])
                        ->default('standard'),

                    TextInput::make('confirmation_sms_number')
                        ->label('Confirmation SMS Number')
                        ->placeholder('Enter phone number for SMS confirmations'),

                    TextInput::make('confirmation_email_address')
                        ->label('Confirmation Email Address')
                        ->email()
                        ->placeholder('Enter email address for confirmations'),

                    TextInput::make('telavox_jwt')
                        ->label('Telavox JWT')
                        ->placeholder('Enter Telavox JWT token'),

                    Select::make('calendar_timezone')
                        ->label('Calendar Timezone')
                        ->options([
                            'Europe/Stockholm' => 'Europe/Stockholm',
                            'Europe/London' => 'Europe/London',
                            'America/New_York' => 'America/New_York',
                            'Asia/Tokyo' => 'Asia/Tokyo',
                            'UTC' => 'UTC',
                        ])
                        ->default('Europe/Stockholm'),

                    Toggle::make('calendar_weekends')
                        ->label('Show Weekends in Calendar')
                        ->default(false),

                ]),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }
}
