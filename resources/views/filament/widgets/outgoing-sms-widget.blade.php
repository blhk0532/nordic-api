<x-filament-widgets::widget>
    <x-filament::section collapsible :collapsed="false">
        <x-slot name="heading"></x-slot>

        <form wire:submit="sendSms">
            {{ $this->form }}

            <div class="flex justify-end mt-4">
                <x-filament::button type="submit" color="primary" style="margin-right: 2rem;">
                    Skicka SMS
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
