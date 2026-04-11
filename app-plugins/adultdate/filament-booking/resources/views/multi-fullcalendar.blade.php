@php
    $plugin = \Adultdate\FilamentBooking\FilamentBookingPlugin::get();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>

<style>
    .filament-fullcalendar {
        min-height: 536px;
        position: relative;
    }
    .fc .fc-timegrid-slot {
    border-bottom: 0px;
    max-height: 1em!important;
        height: 10px!important;
}
.fc .fc-col-header-cell-cushion {
    display: inline-block;
    padding: 4px 0px;
    max-height: 20px;
    overflow: hidden;
    font-size: 0.75rem!important;
    font-weight: 600;
    letter-spacing: 0px
}
.fc-direction-ltr .fc-button-group > .fc-button:not(:first-child) {
    height: 32px;
    padding:4px 12px;
}
.fc-direction-ltr .fc-button-group > .fc-button:not(:last-child) {
 height: 32px;
   padding:4px 12px;
}
.fc .fc-toolbar-title {
    font-size: 1rem!important;
    margin: 0px;
    max-width: 200px;
    padding: 0px 2px;
    display: flex;
    overflow: hidden;
    text-overflow: clip;
    max-height: 24px;
}
.get-heading {

    font-weight: 600;

    }
    header.fi-header {
        display:none!important;
    }
</style>
        <div class="get-heading flex justify-start flex-1 mb-4">
             {{ $this->getHeading() }}

            <x-filament::actions :actions="$this->getCachedHeaderActions()" class="shrink-0" />
        </div>

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
