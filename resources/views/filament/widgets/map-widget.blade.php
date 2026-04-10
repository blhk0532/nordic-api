@php
    $config = $this->getMapData();
@endphp


<x-filament-widgets::widget>

    <x-filament::section
            collapsible
        collapsed>
        <x-slot name="heading" class="flex items-center gap-2 flex-wrap">
<div style="display: flex;">
            <x-filament::icon icon="heroicon-o-map" class="mr-2" />
             {{ $this->getHeading() }}
</div>
        </x-slot>

        <x-filament-leaflet::map
            :config="$config"
            widget
        />

    </x-filament::section>

    <x-filament-actions::modals />

</x-filament-widgets::widget>
