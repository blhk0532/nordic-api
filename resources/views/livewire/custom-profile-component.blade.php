<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="fi-ac fi-align-end mt-4">
            <x-filament::button type="submit">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>
