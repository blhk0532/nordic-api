<x-filament-widgets::widget>
    <x-filament::section
        :heading="static::$heading"
        description="Select the outcome of the call attempt for the selected record."
    >
        @if ($this->record)
            <div class="space-y-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Current:</strong> {{ $this->record->telefon }}<br>
                    <strong>Attempts:</strong> {{ $this->record->attempts ?? 0 }}<br>
                    <strong>Outcome:</strong>
                    @if($this->record->outcome)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($this->record->outcome->value === 'yes') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($this->record->outcome->value === 'no') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @elseif($this->record->outcome->value === 'dmc') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @elseif($this->record->outcome->value === 'no_answer') bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                            @elseif($this->record->outcome->value === 'voicemail') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                            @elseif($this->record->outcome->value === 'not_connected') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @elseif($this->record->outcome->value === 'call_back') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300
                            @elseif($this->record->outcome->value === 'clicked') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                            @elseif(in_array($this->record->outcome->value, ['not_interested', 'wrong_number', 'busy'])) bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @elseif($this->record->outcome->value === 'recently_done') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                            @endif">
                            {{ $this->record->outcome->getLabel() }}
                        </span>
                    @else
                        <span class="text-gray-500">None</span>
                    @endif
                </div>

                <div class="grid grid-cols-4 md:grid-cols-3 gap-2">
                    @foreach(\App\Enums\Outcomes3::cases() as $outcome)
                        <x-filament::button
                            wire:click="selectOutcome('{{ $outcome->value }}')"
                            :color="$outcome->getColor()"
                            size="sm"
                            class="w-full justify-start outcome-button"
                        >
                            <x-heroicon-{{ $outcome->getIcon() }} class="w-4 h-4 mr-2" />
                            {{ $outcome->getLabel() }}
                        </x-filament::button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                <p class="hidden">Select a record from the table to record call outcomes.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
