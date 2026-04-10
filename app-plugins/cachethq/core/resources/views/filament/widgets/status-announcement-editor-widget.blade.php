<x-filament-widgets::widget class="overflow-hidden" id="status-announcement-editor-widget">
    <x-filament::section class="space-y-6">
        <div class="space-y-2">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Use this editor to keep a short announcement or message in front of your status page community.
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
                Save announcement
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
