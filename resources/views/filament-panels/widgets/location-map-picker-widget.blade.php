@php

@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6 relative h-full map-picker-location-widget relative">





   {{ $this->form }}


            <div class="flex justify-end gap-2 relative top-2" style="top:0rem;">
            <div style="left: 0px;font-size:10px;display:none;" class="absolute left-0 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                Lat  {{ data_get($this->data, 'location.lat', '—') }} ⚲
                Lng {{ data_get($this->data, 'location.lng', '—') }}
            </div>
                <x-filament::button
                    id="spara-map-pin-button"
                    wire:click="submit"
                    style="display:none;"
                    color="primary"
                >
                    Spara
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
