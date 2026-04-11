<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Pages;

use App\Models\User;
use App\UserRole;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Concerns\HasTabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use UnitEnum;
use Zap\Facades\Zap;
use Zap\Models\Schedule;

class ManageAppointments extends Page implements HasForms, HasTable
{
    use HasTabs, InteractsWithForms, InteractsWithTable;

    protected string $view = 'filament.pages.manage-appointments';

    protected static ?string $navigationLabel = 'Shedule';

    protected static ?int $sort = 10;

    protected static ?int $navigationSort = 10;

    protected static string|UnitEnum|null $navigationGroup = 'Bokning';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public function mount(): void
    {
        //
    }

    public function table(Table $table): Table
    {
        $schedules = Schedule::where('schedule_type', 'appointment')->where('start_date', '>=', today())->with('periods');
        $schedules = Schedule::query()
            ->where('schedule_type', 'appointment')
            ->where('start_date', '>=', today())
            ->with('periods');

        return $table
            ->query(fn () => $schedules)
            ->columns([
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('time_range')
                    ->label('Time Range')
                    ->getStateUsing(fn ($record) => ($record->periods->first()?->start_time ?? 'N/A').' - '.($record->periods->first()?->end_time ?? 'N/A')
                    ),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->getStateUsing(fn ($record) => $record->name ?? 'N/A'),

                TextColumn::make('service_provider')
                    ->label('Service Provider')
                    ->getStateUsing(fn ($record) => optional(User::find($record->schedulable_id))->name ?? 'N/A')
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('booking')
                    ->label('Booking')
                    ->getStateUsing(fn ($record) => isset($record->metadata['booking_user_id']) ? optional(User::find($record->metadata['booking_user_id']))->name ?? 'N/A' : 'N/A'),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Today')
                    ->query(fn ($query) => $query->whereDate('start_date', today())),
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn ($query) => $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn ($query) => $query->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])),
            ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('Appointment')
                ->label('Add Appointment')
                ->color('success')
                ->schema($this->appointmentForm())
                ->action(function (array $data) {
                    $this->addAppointment($data);
                }),
        ];
    }

    public function appointmentForm(): array
    {
        return [
            DatePicker::make('date')
                ->label('Start Date')
                ->live()
                ->required(),
            TimePicker::make('start_time')
                ->label('Start Time')
                ->seconds(false)
                ->required(),
            TimePicker::make('end_time')
                ->label('End Time')
                ->seconds(false)
                ->required(),
            Select::make('service_user_id')
                ->label('ServiceProvider ')
                ->options(function () {
                    return User::where('role', UserRole::SERVICE->value)->pluck('name', 'id')->toArray();
                })
                ->live()
                ->required(),
            Select::make('booking_user_id')
                ->label('Booking')
                ->options(function () {
                    return User::where('role', UserRole::BOOKING->value)->pluck('name', 'id')->toArray();
                })
                ->required(),
            TextInput::make('reason')
                ->label('Reason')
                ->required(),

            Section::make()
                ->schema(function (Get $get) {
                    /** @var User $serviceProvider */
                    $serviceProvider = User::find($get('service_user_id'));
                    if (! $serviceProvider) {
                        return [];
                    }
                    $slots = $serviceProvider->getBookableSlots($get('date'), 60, 15);
                    if (! $slots) {
                        return [];
                    }

                    return [
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->seconds(false)
                            ->datalist(
                                collect($slots)
                                    ->filter(fn ($s) => $s['is_available'])
                                    ->map(fn ($s) => ($s['start_time']))
                                    ->values()
                            )
                            ->reactive()
                            ->required(),
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->seconds(false)
                            ->datalist(
                                collect($slots)
                                    ->filter(fn ($s) => $s['is_available'])
                                    ->map(fn ($s) => ($s['end_time']))
                                    ->values()
                            )
                            ->reactive()
                            ->required(),
                    ];
                })
                ->reactive()
                ->columns(2),
        ];
    }

    public function addAppointment(array $data): void
    {
        $serviceProvider = User::find($data['service_user_id']);
        if (! $serviceProvider) {
            Notification::make()
                ->title('Service Provider not found.')
                ->danger()
                ->send();

            return;
        }
        if (empty($data['start_time']) || empty($data['end_time'])) {
            Notification::make()
                ->title('Please select a start and end time for the appointment.')
                ->danger()
                ->send();

            return;
        }
        // Logic to add appointment schedule
        Zap::for($serviceProvider)
            ->named($data['reason'] ?? 'Add Appointment')
            ->appointment()
            ->from($data['date'])
            ->addPeriod($data['start_time'], $data['end_time'])
            ->withMetadata(['booking_user_id' => $data['booking_user_id']])
            ->save();

        Notification::make()
            ->title('Appointment schedule added successfully.')
            ->success()
            ->send();

        $this->resetTable();
    }
}
