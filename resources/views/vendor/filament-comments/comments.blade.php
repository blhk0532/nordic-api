<div class="space-y-2">
    <form wire:submit.prevent="save" x-data="{ wasFocused: false }">
        {{-- tiptap editor --}}
        <div class="relative tip-tap-container mb-2" id="tip-tap-container" x-on:click="wasFocused = true">
            <div x-data="editor(@js($commentBody), @js($mentionables), 'comments')" wire:ignore>
                <div x-ref="element"></div>
            </div>
        </div>

        <div x-show="wasFocused">
            <x-filament::button
                wire:click="save"
                size="sm"
            >Save</x-filament::button>

            <x-filament::button
                x-on:click="wasFocused = false"
                wire:click="clear"
                size="sm"
                 color="gray"
            >Cancel</x-filament::button>
        </div>
    </form>

    <livewire:filament-comments::comment-list :record="$record" />
</div>

