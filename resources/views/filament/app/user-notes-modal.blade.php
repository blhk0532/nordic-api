<style>
.fi-input-wrp.fi-fo-rich-editor {
    min-height: 20vh;
}
#user-notes-modal{
position: absolute;
}
</style>
<div
    class="w-full"
    x-init="$nextTick(() => window.dispatchEvent(new Event('resize')))"
    x-on:calendar-resize.window="window.dispatchEvent(new Event('resize'))"
    x-on:open-modal.window="if ($event.detail && $event.detail.id === 'user-notes-modal') { setTimeout(() => window.dispatchEvent(new Event('resize')), 50); setTimeout(() => window.dispatchEvent(new Event('resize')), 250); setTimeout(() => window.dispatchEvent(new Event('resize')), 400); }"
>
    <div class="user-notes-widget-wrapper m-1" id="user-notes-widget-wrapper">

            <form class="space-y-6" wire:submit.prevent="save">
                {{ $this->form }}

                <div class="flex justify-end">
                    <x-filament::button type="submit">
                        Spara Anteckningar
                    </x-filament::button>
                </div>
            </form>

    </div>
</div>
