<x-filament-panels::page>
    <form wire:submit="save">
        <x-filament::section>
            <x-slot name="heading">
                Kalender Inställningar
            </x-slot>

            <x-slot name="description">
                Konfigurera öppettider för kalendern. Dessa inställningar används i dagvyn och veckovyn för att begränsa tidsintervallet.
            </x-slot>

            <div class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit">
                    Spara
                </x-filament::button>
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>
