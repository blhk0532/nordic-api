<x-filament-widgets::widget>
    <x-filament::section>
        <style>

        </style>
        @if($record && ($record->latitud || $record->longitude))
            <div class="space-y-4" wire:key="pinpoint-form-{{ $record->id }}">
                {{ $this->form }}
            </div>
        @else
            <div class="flex items-center justify-center h-64 text-gray-500" style="  min-height: 440px;">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="mt-2 text-sm">{{ $record->gatuadress }}</p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
