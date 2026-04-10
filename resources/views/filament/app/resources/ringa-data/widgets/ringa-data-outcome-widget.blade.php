<x-filament-widgets::widget class="h-full ringa-data-outcome-widget">
    <x-filament::section class="h-full">
        <div class="h-full flex flex-col justify-between">
            @if ($this->record)
                <div class="space-y-4 flex-grow">
                    <!-- Status & History Section -->
                    <div class="flex items-center gap-4 pb-4 mb-0 border-b border-gray-100 dark:border-white/5">
                        @if ((is_array($this->record->telfonnummer) ? count($this->record->telfonnummer) : 0) < 5)
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl shrink-0 bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-7 h-7" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Senaste Utfall</h3>
                                @if($this->record->outcome)
                                    <x-filament::badge
                                        :color="$this->record->outcome->getColor()"
                                        :icon="$this->record->outcome->getIcon()"
                                        size="sm"
                                    >
                                        {{ $this->record->outcome->getLabel() }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-xs text-gray-400 italic font-medium">Inget utfall än</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-bold">Försök:</span> {{ $this->record->attempts ?? 0 }}
                                </span>
                                @if($this->record->outcome)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-bold">Senast:</span> {{ $this->record->updated_at->format('l H:i') }}
                                    </span>
                                @endif
                                @if($this->record->aterkom_at)
                                    <span class="text-xs text-primary-600 dark:text-primary-400 font-bold">
                                        Återkommer: {{ $this->record->aterkom_at->format('l H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                                {{ $this->addPhoneNumbersAction() }}
                        @endif
                    </div>

                    <!-- Call Actions -->
                    @php
                        $telfonnummer = $this->record->telfonnummer ?? [];
                        $telefon = $this->record->telefon;

                        // Build phone numbers array from both fields
                        $phoneNumbers = [];

                        // Handle telfonnummer if it's an array
                        if (is_array($telfonnummer) && !empty($telfonnummer)) {
                            $phoneNumbers = $telfonnummer;
                        }

                        // Handle telefon field if it exists and is not already in telfonnummer
                        if ($telefon) {
                            if (is_array($telefon)) {
                                foreach ($telefon as $phone) {
                                    if (!in_array($phone, $phoneNumbers)) {
                                        $phoneNumbers[] = $phone;
                                    }
                                }
                            } elseif (is_string($telefon) && $telefon !== '') {
                                if (!in_array($telefon, $phoneNumbers)) {
                                    $phoneNumbers[] = $telefon;
                                }
                            }
                        }
                    @endphp

                    @if (!empty($phoneNumbers))
                        <div class="space-y-3 p-3 rounded-xl ">
                            <h4 class="hidden text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-2 hidden">
                                <x-filament::icon icon="heroicon-m-phone" class="w-3.5 h-3.5 hidden" />
                            </h4>
                            <div class="grid grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($phoneNumbers as $phone)
                                    @php $dialable = preg_replace('/\s+/', '', $phone); @endphp
                                    <x-filament::button
                                        tag="a"
                                        href="tel:{{ $dialable }}"
                                        color="gray"
                                        wire:click="queueOutcall('{{ addslashes($phone) }}')"
                                        size="sm"
                                        class="w-full justify-start shadow-sm hover:bg-success-50 dark:hover:bg-gray-500/50 group transition-colors"
                                    >
                                        <span class="text-xs font-medium group-hover:text-primary-600 dark:group-hover:text-white">{{ $phone }}</span>
                                    </x-filament::button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 p-3 text-sm text-warning-600 rounded-lg bg-warning-50 dark:bg-warning-500/10">
                            <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-5 h-5" />
                            <span>Inga telefonnummer tillgängliga.</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-6 text-center text-gray-500 dark:text-gray-400 flex-grow flex items-center justify-center">
                    <p  class="hidden">Select a record from the table to record call outcomes.</p>
                </div>
            @endif
        </div>
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
