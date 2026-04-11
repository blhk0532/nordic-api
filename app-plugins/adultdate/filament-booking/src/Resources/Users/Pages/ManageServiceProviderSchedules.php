<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Users\Pages;

use Adultdate\FilamentBooking\Users\UserResource;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Zap\Enums\ScheduleTypes;
use Zap\Facades\Zap;

class ManageServiceProviderSchedules extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithRecord, InteractsWithTable;

    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.manage-service-provider-schedules';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->record->schedules()->with('periods')->getQuery())
            ->columns([
                TextColumn::make('reason')
                    ->label('Reason')
                    ->getStateUsing(fn ($record) => $record->name),
                TextColumn::make('schedule_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (ScheduleTypes $state) => match ($state) {
                        ScheduleTypes::AVAILABILITY => 'success',
                        ScheduleTypes::BLOCKED => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('periods.start_time')
                    ->label('Start Time')
                    ->isoTime()
                    ->sortable(),
                TextColumn::make('periods.end_time')
                    ->label('End Time')
                    ->isoTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('schedule_type')
                    ->label('Type')
                    ->options([
                        'availability' => 'Availability',
                        'blocked' => 'Blocked',
                        'appointment' => 'Appointment',
                    ]),
            ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('Availability')
                ->label('Add Availability')
                ->color('success')
                ->schema($this->availabilityForm())
                ->action(function (array $data) {
                    $this->addAvailability($data);
                }),
            Action::make('Block')
                ->label('Add Block')
                ->color('danger')
                ->schema($this->blockForm())
                ->action(function (array $data) {
                    $this->addBlock($data);
                }),
        ];
    }

    public function availabilityForm(): array
    {
        return [
            TextInput::make('reason')
                ->label('Reason')
                ->required(),
            CheckboxList::make('days_of_week')
                ->label('Days of the Week')
                ->options([
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ])
                ->required(),
            TimePicker::make('start_time')
                ->required()
                ->default('07:00')
                ->seconds(false)
                ->minutesStep(15),
            TimePicker::make('end_time')
                ->required()
                ->default('21:00')
                ->seconds(false)
                ->minutesStep(15),
            DatePicker::make('start_date')
                ->label('Start Date')
                ->default(today())
                ->required(),
            DatePicker::make('end_date')
                ->label('End Date')
                ->default(today()->addMonth())
                ->required(),
        ];
    }

    public function blockForm(): array
    {
        return [
            TextInput::make('reason')
                ->label('Reason')
                ->required(),
            CheckboxList::make('days_of_week')
                ->label('Days of the Week')
                ->options([
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ])
                ->required(),
            TimePicker::make('start_time')
                ->required()
                ->seconds(false)
                ->minutesStep(15),
            TimePicker::make('end_time')
                ->required()
                ->seconds(false)
                ->minutesStep(15),
            DatePicker::make('start_date')
                ->label('Start Date')
                ->required(),
            DatePicker::make('end_date')
                ->label('End Date')
                ->required(),
        ];
    }

    public function addAvailability(array $data): void
    {
        // Logic to add availability schedule
        Zap::for($this->record)
            ->named($data['reason'] ?? 'Add Availability')
            ->availability()
            ->from($data['start_date'])
            ->to($data['end_date'])
            ->addPeriod($data['start_time'], $data['end_time'])
            ->weekly($data['days_of_week'] ?? [])
            ->save();

        Notification::make()
            ->title('Availability schedule added successfully.')
            ->success()
            ->send();

        $this->resetTable();
    }

    public function addBlock(array $data): void
    {
        // Logic to add block schedule
        Zap::for($this->record)
            ->named($data['reason'] ?? 'Add Block')
            ->blocked()
            ->from($data['start_date'])
            ->to($data['end_date'])
            ->addPeriod($data['start_time'], $data['end_time'])
            ->weekly($data['days_of_week'] ?? [])
            ->save();
    }
}
