<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Concerns\HasHeaderActions;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Adultdate\FilamentBooking\FilamentBookingPlugin;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class BookingFullCalendarWidget extends Widget implements HasActions, HasForms
{
    use CanBeConfigured, HasHeaderActions, InteractsWithRawJS;
    use InteractsWithActions;
    use InteractsWithForms;

    public $selectedTechnician;

    /**
     * Blade view used by this widget (NON-static in Filament v3)
     */
    protected string $view = 'filament-booking::fullcalendar';

    protected static ?int $sort = 4;

    /**
     * Provide a default fetchEvents method so Livewire calls will always exist
     * on the base widget. Specific calendar widgets may override this.
     */
    public function fetchEvents(array $info): array
    {
        return [];
    }

    public function onDateClickJs(array $data): void
    {
        if (method_exists($this, 'onDateClick')) {
            $this->onDateClick(
                $data['dateStr'] ?? $data['date'],
                $data['allDay'] ?? false,
                $data['view'] ?? null,
                $data['resource'] ?? null
            );
        }
    }

    public function getTechnicianOptions(): array
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['technician', 'admin', 'super_admin']);
        })->pluck('name', 'id')->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('selectedTechnician')
                    ->label('Tekniker')
                    ->options($this->getTechnicianOptions())
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->updatedSelectedTechnician();
                    }),
            ]);
    }
    /**
     * Widget width
     */
    //    protected int | string | array $columnSpan = 'full';

    /**
     * Provide view data from the Booking plugin
     */
    protected function getViewData(): array
    {
        /** @var FilamentBookingPlugin|null $plugin */
        $plugin = filament()
            ->getCurrentPanel()
            ?->getPlugin('adultdate-booking');

        return [
            // FullCalendar plugins
            'plugins' => $plugin?->getPlugins() ?? [
                'dayGrid',
                'timeGrid',
                'interaction',
                'list',
            ],

            // Localization
            'timezone' => $plugin?->getTimezone(),
            'locale' => $plugin?->getLocale(),

            // Interaction flags
            'editable' => $plugin?->isEditable() ?? false,
            'selectable' => $plugin?->isSelectable() ?? false,

            // Scheduler license key (optional)
            'schedulerLicenseKey' => $plugin?->getSchedulerLicenseKey(),

            // Additional FullCalendar configuration
            'config' => $plugin?->getConfig() ?? [],
        ];
    }

    protected function headerActions(): array
    {
        return [
            Action::make('create'),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Action::make('edit'),
            Action::make('delete'),
        ];
    }

    protected function viewAction(): Action
    {
        // Use a non-colliding action name so it doesn't overwrite the widget's `$view` property
        return Action::make('viewEvent');
    }
}
