@php
    $plugin = \Adultdate\FilamentBooking\FilamentBookingPlugin::get();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex justify-end flex-1 mb-4">
            <x-filament::actions :actions="$this->getCachedHeaderActions()" class="shrink-0" />
                {{ $this->getHeading() ? $this->getHeading() : '' }}
        </div>

            <x-slot name="heading">
                {{ $this->getHeading() }}
            </x-slot>

        {{-- Ensure the built Alpine component is available (fallback to public/vendor) --}}
        <script defer src="{{ asset('plugins/adultdate/filament-booking/dist/filament-fullcalendar.js') }}"></script>

        <div wire:ignore x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-fullcalendar-alpine', 'adultdate/filament-booking') }}"
            x-ignore x-data="fullcalendar({
                locale: @js($plugin->getLocale()),
                plugins: @js($plugin->getPlugins()),
                schedulerLicenseKey: @js($plugin->getSchedulerLicenseKey()),
                timeZone: @js($plugin->getTimezone()),
                config: @js($this->getConfig()),
                editable: @json($plugin->isEditable()),
                selectable: @json($plugin->isSelectable()),
                eventClassNames: {!! htmlspecialchars($this->eventClassNames(), ENT_COMPAT) !!},
                eventContent: {!! htmlspecialchars($this->eventContent(), ENT_COMPAT) !!},
                eventDidMount: {!! htmlspecialchars($this->eventDidMount(), ENT_COMPAT) !!},
                eventWillUnmount: {!! htmlspecialchars($this->eventWillUnmount(), ENT_COMPAT) !!},
            })" class="filament-fullcalendar" data-filament-fullcalendar="true"></div>
                            <x-filament-booking::context-menu/>

    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
