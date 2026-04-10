<x-filament-panels::page>
    @if(session('status'))
        <div class="mb-4">
            <div class="rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-700">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        <form wire:submit.prevent="send">
            <div class="grid grid-cols-1 gap-4">
                {{ $this->form }}
                <div>
                    <x-filament::button type="submit">Skicka SMS</x-filament::button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
