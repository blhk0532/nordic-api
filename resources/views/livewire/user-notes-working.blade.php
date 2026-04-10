<div>
    <x-filament::modal
        id="user-notes-working-modal"
        slide-over
        width="2xl"
        :close-by-clicking-away="false"
        :visible="$slideOverOpen"
    >
        <x-slot name="heading">
            My Notes
        </x-slot>

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="closeModal"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    Save
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</div>
