<style>
.fi-input-wrp.fi-fo-rich-editor {
    min-height: 20vh;
}
</style>

<div
    class="w-full"
    x-init="$nextTick(() => window.dispatchEvent(new Event('resize')))"
    x-on:open-modal.window="if ($event.detail && $event.detail.id === 'user-notes-modal') {
        $nextTick(() => {
            window.dispatchEvent(new Event('resize'));
            setTimeout(() => window.dispatchEvent(new Event('resize')), 100);
            setTimeout(() => window.dispatchEvent(new Event('resize')), 300);
        });
    }"
>
    <div class="user-notes-widget-wrapper m-1">
        <form class="space-y-6" wire:submit="save" @submit.prevent>
            {{ $this->form }}

            <div class="flex justify-end">
                <x-filament::button type="submit">
                    Save Notes
                </x-filament::button>
            </div>
        </form>
    </div>
</div>


