<x-filament-widgets::widget class="h-full ringa-data-infolist-widget">
    <x-filament::section class="h-full">
        @if ($this->record)
            {{ $this->infolist }}
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                Välj en post för att visa detaljer.
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
