<x-filament-widgets::widget>
    <x-filament::section heading="Dialpad" description="Compact outbound dialer for agents">
        <div class="mx-auto w-full max-w-sm space-y-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Campaign</label>
                <select wire:model.live="campaignId" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Select campaign</option>
                    @foreach ($this->campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-center text-lg font-semibold tracking-widest dark:border-gray-700 dark:bg-gray-800">
                {{ $number !== '' ? $number : 'Enter number' }}
            </div>

            <div class="grid grid-cols-3 gap-2">
                @foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '0', '#'] as $digit)
                    <button
                        type="button"
                        wire:key="dialpad-digit-{{ $digit }}"
                        wire:click="appendDigit('{{ $digit }}')"
                        class="rounded-lg border border-gray-200 bg-white py-2 text-sm font-semibold transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800"
                    >
                        {{ $digit }}
                    </button>
                @endforeach
            </div>

            <div class="grid grid-cols-3 gap-2">
                <button type="button" wire:click="clear" class="rounded-lg bg-gray-200 px-3 py-2 text-sm font-medium dark:bg-gray-700">
                    Clear
                </button>
                <button type="button" wire:click="backspace" class="rounded-lg bg-gray-200 px-3 py-2 text-sm font-medium dark:bg-gray-700">
                    Del
                </button>
                <button type="button" wire:click="placeCall" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    Call
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
