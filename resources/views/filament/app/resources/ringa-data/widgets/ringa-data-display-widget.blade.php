<x-filament-widgets::widget>
    <style>
    h2#fc-dom-1 {
         position: absolute;
  right: 24rem;
  top: 4px;
        }
        .fi-btn{
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.8rem;
        }
                .fi-outcome-btn{
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.8rem;
        }
        .fi-fo-field-label-ctn {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fi-fo-field-label-content {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fi-text-overflow-ellipsis {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    <x-filament::section>
        @if ($this->record)
            <div class="space-y-4">
                <!-- Compact Profile Header -->
                <div class="flex items-center gap-2 pb-4 border-b border-gray-100 dark:border-white/5">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl shrink-0 bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400">
                        <x-filament::icon
                            icon="{{ $this->record->kon === 'F' ? 'heroicon-o-user-circle' : 'heroicon-o-user' }}"
                            class="w-8 h-8"
                        />
                    </div>
                    <div class="flex-1 min-w-0 overflow-hidden">
                        <div class="flex flex-wrap items-baseline gap-x-3 overflow-hidden">
                            <h2 class="text-xl font-bold text-gray-950 dark:text-white truncate">
                                {{ $this->record->fornamn }} {{ $this->record->efternamn }}
                            </h2>
                            <span class="text-sm font-mono text-gray-500 dark:text-gray-400 select-all">
                                {{ $this->record->alder ?? '-' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-1" style="display: ruby;">

                            @if($this->record->kon)
                                <x-filament::badge size="sm" color="gray" icon="heroicon-m-user">
                                    {{ $this->record->kon === 'F' ? 'Kvinna' : 'Man' }}
                                </x-filament::badge>
                            @endif
                            @if($this->record->civilstand)
                                <x-filament::badge size="sm" color="gray" icon="heroicon-m-heart">
                                    {{ $this->record->civilstand }}
                                </x-filament::badge>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 pb-2">
                    <!-- Address Group -->
                    <div class="p-4 pb-2 rounded-xl bg-gray-50/50 dark:bg-white/5">

                        <div class="space-y-3">
                                <div class="pt-2 mt-0">
                            <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Förnamn</p>
                                <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->fornamn ?? '-' }}</p>
                            </div>
</div>
                                <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5 hidden">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Efternamn</p>
                                    <p class="text-sm font-semiboldfi-text-overflow-ellipsis">{{ $this->record->efternamn ?? '-' }}</p>
                                </div>
</div>

                        </div>
                    </div>

                    <!-- Housing Group -->
                    <div class="p-4 pb-2 rounded-xl bg-gray-50/50 dark:bg-white/5">

                        <div class="space-y-3">
 <div class="pt-2 mt-0 ">

                                   <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Efternamn</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->efternamn ?? '-' }}</p>
                                </div>

                            <div class="hidden">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Ålder</p>
                                <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->alder ?? '-' }}</p>

                            </div>

                            </div>
 <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5 hidden">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Kön</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis"> {{ $this->record->kon === 'F' ? 'Kvinna' : 'Man' }}</p>
                                </div>
                                </div>

                        </div>
                    </div>

                    <!-- Assets & Contact Group -->
                    <div class="p-4 pb-2 rounded-xl bg-gray-50/50 dark:bg-white/5">

                        <div class="space-y-3">
                         <div class="pt-2 mt-0">
                             <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Födelsedag</p>

                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->fodelsedag->format('Y-m-d') ?? '-' }}</p>

                                </div>

                            </div>
                            <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5 hidden">
                                 <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Civilstånd</p>

                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->civilstand ?? '-' }}</p>

  overflow: hidden;">{{ $this->record->civilstand ?? '-' }}</p>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-gray-50/50 dark:bg-white/5">
                        <div class="items-center gap-2 mb-3 text-primary-600 dark:text-primary-400 hidden">
                            <x-filament::icon icon="heroicon-o-map-pin" class="w-5 h-5" />
                            <h4 class="text-sm font-bold uppercase tracking-wider">Adress</h4>
                        </div>
                        <div class="space-y-3">
                                <div class="pt-1 mt-1 border-gray-100 dark:border-white/5">
                            <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Gatuadress</p>
                                <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->gatuadress ?? '-' }}</p>
                            </div>
</div>
                                <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Postkod</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->postnummer ?? '-' }}</p>
                                </div>
</div>
                                    <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Ort</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->postort ?? '-' }}</p>
                                </div>
</div>
                                    <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                            <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Kommun</p>
                                <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->kommun ?? '-' }}</p>
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Housing Group -->
                    <div class="p-4 rounded-xl bg-gray-50/50 dark:bg-white/5">
                        <div class="items-center gap-2 mb-3 text-warning-600 dark:text-warning-400 hidden">
                            <x-filament::icon icon="heroicon-o-home" class="w-5 h-5" />
                            <h4 class="text-sm font-bold uppercase tracking-wider">Bostad</h4>
                        </div>
                        <div class="space-y-3">
 <div class="pt-1 mt-1 border-gray-100 dark:border-white/5">
                            <div>
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Bostadstyp</p>
                                <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->bostadstyp ?? '-' }} ({{ $this->record->agandeform ?? '-' }})</p>
                            </div>
                            </div>
 <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Byggår</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->byggar ?? '-' }}</p>
                                </div>
                                </div>
 <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Flyttdag</p>
                                    <p class="text-sm font-semibold fi-text-overflow-ellipsis">{{ $this->record->adressandring ?? '-' }}</p>
                                </div>
</div>
                            <div class="pt-2 mt-2 border-t border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Boarea</p>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-semibold">{{ $this->record->boarea }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assets & Contact Group -->
                    <div class="p-4 rounded-xl bg-gray-50/50 dark:bg-white/5">
                        <div class="items-center gap-2 mb-3 text-success-600 dark:text-success-400 hidden">
                            <x-filament::icon icon="heroicon-o-briefcase" class="w-5 h-5" />
                            <h4 class="text-sm font-bold uppercase tracking-wider">Status</h4>
                        </div>
                        <div class="space-y-1">
                                                        <div class="pt-1 mt-1 border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Personer</p>
                                <div class="flex items-center gap-1">
                                    <span class="text-lg font-bold">{{ is_array($this->record->personer) ? count($this->record->personer) : 0 }}</span>
                                    <x-filament::icon icon="heroicon-m-users" class="w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                         <div class="pt-2 mt-0 border-t border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Bolag</p>
                                <div class="flex items-center gap-1">
                                    <span class="text-lg font-bold fi-text-overflow-ellipsis">{{ is_array($this->record->bolagsengagemang) ? count($this->record->bolagsengagemang) : 0 }}</span>
                                    <x-filament::icon icon="heroicon-m-building-office-2" class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                            <div class="pt-2 mt-0 border-t border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Fordon</p>
                                <div class="flex items-center gap-1">
                                    <span class="text-lg font-bold">{{ is_array($this->record->fordon) ? count($this->record->fordon) : 0 }}</span>
                                    <x-filament::icon icon="heroicon-m-truck" class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                           <div class="pt-2 mt-0 border-t border-gray-100 dark:border-white/5">
                                <p class="text-xs text-gray-500 uppercase dark:text-gray-400">Hundar</p>

                                    <div class="flex items-center gap-1">
                                    <span class="text-lg font-bold fi-text-overflow-ellipsis">{{ is_array($this->record->hundar) ? count($this->record->hundar) : 0 }}</span>
                                    <x-filament::icon icon="heroicon-m-bug-ant" class="w-4 h-4 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400" style="min-height: 256px;">
                <p class="hidden">Select a record from the table to view details.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
