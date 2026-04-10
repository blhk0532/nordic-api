<x-filament-widgets::widget class="h-full ringa-data-infolist-phone-numbers-widget">
    <x-filament::section class="h-full">
        @if ($this->record)
            {{ $this->infolist }}
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
