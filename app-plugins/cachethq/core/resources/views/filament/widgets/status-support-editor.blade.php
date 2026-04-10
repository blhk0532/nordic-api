<x-filament-widgets::widget class="overflow-hidden" id="status-support-editor-widget">
    <x-filament::section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ $supportingHeading }}
            </p>
        </div>

        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button
                icon="heroicon-o-check"
                color="success"
                wire:click="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                Save Dashboard Settings
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
