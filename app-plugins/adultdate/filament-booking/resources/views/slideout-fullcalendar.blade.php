@php
    $plugin = \Adultdate\FilamentBooking\FilamentBookingPlugin::get();
@endphp
<x-filament-widgets::widget>
    <x-filament::section>

        <div wire:ignore x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-fullcalendar-alpine', 'adultdate/filament-booking') }}"
            x-ignore x-data="fullcalendar({
                locale: @js($plugin->getLocale()),
                plugins: @js($plugin->getPlugins()),
                dayCount: @js($plugin->getDayCount()),
                weekends: @js($plugin->getWeekends()),
                schedulerLicenseKey: @js($plugin->getSchedulerLicenseKey()),
                timeZone: @js($plugin->getTimezone()),
                config: @js($this->getConfig()),
                editable: @json($plugin->isEditable()),
                selectable: @json($plugin->isSelectable()),
                eventClassNames: {!! htmlspecialchars($this->eventClassNames(), ENT_COMPAT) !!},
                eventContent: {!! htmlspecialchars($this->eventContent(), ENT_COMPAT) !!},
                eventDidMount: {!! htmlspecialchars($this->eventDidMount(), ENT_COMPAT) !!},
                eventWillUnmount: {!! htmlspecialchars($this->eventWillUnmount(), ENT_COMPAT) !!},
            })" class="filament-fullcalendar {{ $this->getCalendarClass() }}" data-filament-fullcalendar="true"></div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
