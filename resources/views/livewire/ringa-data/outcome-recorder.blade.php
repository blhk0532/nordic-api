<div class="h-full">
    <div class="outcome-recorder space-y-6 h-full flex flex-col">
        @if($record)
            <div class="space-y-4 flex-grow">
                @php
                    $outcomes = \App\Models\OutcomeSetting::where('is_active', true)
                        ->orderByRaw('CASE WHEN `order` = 0 THEN 999999 ELSE `order` END ASC')
                        ->orderBy('created_at')
                        ->get();
                @endphp

                @if($outcomes->count() > 0)
                <div class="space-y-2">
                    <div class="grid grid-cols-4 gap-2 items-stretch">
                        @foreach($outcomes as $outcome)
                            <div class="w-full h-full flex" wire:key="outcome-{{ $outcome->id }}">
                                @if($outcome->outcome === 'RingTillbaka')
                                    {{ $this->returnCallAction }}
                                @elseif($outcome->outcome === 'Aterkommer')
                                    {{ $this->aterkommerAction }}
                                @elseif($outcome->outcome === 'NyligenGjort')
                                    {{ $this->nextGangAction }}
                                @elseif($outcome->outcome === 'Offert')
                                    {{ $this->offertAction }}
                                @elseif($outcome->outcome === 'Bokad')
                                    {{ $this->bokadAction }}
                                @elseif($outcome->outcome === 'Kontakt')
                                    {{ $this->kontaktAction }}
                                @else
                                    <button
                                        wire:click="recordOutcome('{{ $outcome->outcome }}')"
                                        wire:target="recordOutcome('{{ $outcome->outcome }}')"
                                        wire:loading.attr="disabled"
                                        @if($processingOutcome === $outcome->outcome) disabled @endif
                                        style="height:36px;; background-color: {{ $outcome->color }} !important; color: white !important;{{ $processingOutcome === $outcome->outcome ? ' opacity: 0.5;' : '' }}"
                                        class="overflow-hidden whitespace-nowrap w-full h-full px-3 py-2 rounded-lg font-semibold text-sm shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span class="fi-outcome-btn" wire:loading.remove wire:target="recordOutcome('{{ $outcome->outcome }}')">{{ $outcome->title ?? $outcome->type }}</span>
                                        <span wire:loading wire:target="recordOutcome('{{ $outcome->outcome }}')" class="flex items-center justify-center gap-2">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>

                                        </span>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <div class="p-4 text-center text-gray-500">
                        No outcome settings found
                    </div>
                @endif
            </div>
        @else
            <div class="p-4 text-center text-gray-500 flex-grow flex items-center justify-center">

            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
