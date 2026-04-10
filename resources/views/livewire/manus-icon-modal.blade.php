<div
    class="w-full max-w-5xl mx-auto"
    x-init="$nextTick(() => window.dispatchEvent(new Event('resize')))"
    x-on:calendar-resize.window="window.dispatchEvent(new Event('resize'))"
    x-on:open-modal.window="if ($event.detail && $event.detail.id === 'calendar-modal') { setTimeout(() => window.dispatchEvent(new Event('resize')), 50); setTimeout(() => window.dispatchEvent(new Event('resize')), 250); setTimeout(() => window.dispatchEvent(new Event('resize')), 400); }"
        >
    <div class="manus-widget-wrapper m-1" id="manus-widget-wrapper">
            <form class="space-y-6" wire:submit="save" @submit.prevent>
                {{ $this->form }}

                <div class="flex justify-start">
                    <x-filament::button type="submit">
                        Spara Anteckning
                    </x-filament::button>
                </div>
            </form>
    </div>
</div>
