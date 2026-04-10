<x-filament-widgets::widget class="overflow-hidden" id="announcement-editor-widget">
    <x-filament::section :heading="$editingId ? 'Edit Announcement' : now()->format('D j M Y H:i:s')">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end gap-2 mt-4">
                @if($editingId)
                    <x-filament::button
                        color="gray"
                        wire:click="resetForm"
                        type="button"
                    >
                        Cancel
                    </x-filament::button>
                @endif

                <x-filament::button
                    type="submit"
                    wire:loading.attr="disabled"
                >
                    {{ $editingId ? 'Update' : 'Create' }} Announcement
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
