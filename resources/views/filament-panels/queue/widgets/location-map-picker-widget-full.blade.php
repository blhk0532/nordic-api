@php

@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6 relative h-full map-picker-location-widget relative">

<style>
  .filament-google-maps-widget-table  .fi-fo-field-label-col{
    display:none!important;
}
.location-details-section .fi-section{
    background: transparent;
  border: none;
  box-shadow: none;
}
  .location-details-section  .fi-fo-field-label-col{
    display:none!important;
}

.pin-location-section .fi-fo-field-label-col{
    display:none!important;
}
.pin-location-section  .fi-section{
    background: transparent;
  border: none;
  box-shadow: none;
}

.location-map-picker-form-2{
    margin: 0px!important;
  padding: 0px!important;
}
.map-picker-section .fi-section-content{
    margin: 0px!important;
  padding: 0px!important;
}
div.location-map-picker-form-0{
    margin: 0px!important;
  padding: 0px!important;
}
.map-picker-section .fi-fo-field-label-col{
    display:none!important;
}

.location-map-picker-form-0{
    margin: 0px!important;
  padding: 0px!important;
}

.map-picker-location-widget .location-map-picker-form-0{
    margin: 0px!important;
  padding: 0px!important;
}

</style>



            <div wire:key="location-map-picker-form-{{ $this->mapRefreshKey }}">
                {{ $this->form }}
            </div>


            <div class="flex justify-end gap-2 relative" style="top:0rem;margin-bottom: -50px;">
                <div>
                <x-filament::button
                    wire:click="savePin"
                    color="success"
                    size="sm"
                    icon="heroicon-o-map-pin"
                    class="spara-pin-knapp absolute"
                    style="position: absolute;
  left: 24px;
  bottom: 70px;
  z-index: 10;"
                >
                    Save Pin
                </x-filament::button>
</div>
            <div style="left: 0px;font-size:10px;display:none;" class="absolute left-0 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                Lat  {{ data_get($this->data, 'location.lat', '—') }} ⚲
                Lng {{ data_get($this->data, 'location.lng', '—') }}
            </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
