<x-filament-panels::page>
    <div class="space-y-6" wire:poll.5s>
        <!-- Compact Header: Dialpad + Quick Stats -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Dialpad: Compact on Left -->
            <div class="lg:col-span-1">
                @livewire(\App\Filament\Widgets\DialpadWidget::class)
            </div>

            <!-- Quick Stats: Right -->
            <div class="space-y-3 lg:col-span-2">
                <div class="grid gap-3 grid-cols-3">
                    <x-filament::section class="col-span-1 !p-3">
                        <div class="space-y-1 text-center">
                            <p class="text-xs font-medium text-gray-500">Active Calls</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ \App\Models\DialerCallAttempt::where('status', 'in_progress')->count() }}</p>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="col-span-1 !p-3">
                        <div class="space-y-1 text-center">
                            <p class="text-xs font-medium text-gray-500">Pending</p>
                            <p class="text-2xl font-bold text-amber-600">{{ \App\Models\DialerLead::where('status', 'pending')->count() }}</p>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="col-span-1 !p-3">
                        <div class="space-y-1 text-center">
                            <p class="text-xs font-medium text-gray-500">Success %</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format(\App\Models\DialerCallAttempt::where('status', 'completed')->count() / max(\App\Models\DialerCallAttempt::count(), 1) * 100, 0) }}%</p>
                        </div>
                    </x-filament::section>
                </div>

                <!-- Active Campaign Alert -->
                @php
                    $activeCampaign = \App\Models\DialerCampaign::where('status', 'running')->latest('id')->first();
                @endphp
                @if($activeCampaign)
                    <x-filament::section class="border-emerald-200 bg-emerald-50 !p-3">
                        <p class="text-sm font-medium text-emerald-900">
                            <span class="font-bold">{{ $activeCampaign->name }}</span> 
                            • {{ $activeCampaign->pending_leads_count ?? 0 }} pending 
                            • {{ $activeCampaign->active_attempts_count ?? 0 }} active
                        </p>
                    </x-filament::section>
                @else
                    <x-filament::section class="border-l-4 border-amber-400 bg-slate-900 !p-4">
                        <div class="text-center py-3">
                            <p class="text-lg font-semibold text-amber-400">📞 Create a Campaign to Begin</p>
                            <p class="text-sm text-slate-300 mt-2">Set up a new dialing campaign and start it to begin making calls.</p>
                        </div>
                    </x-filament::section>
                @endif
            </div>
        </div>

        <!-- Forms: Create Campaign & Import Leads -->
        <div class="grid gap-6 lg:grid-cols-2">
            <x-filament::section heading="Create Campaign">
                <form wire:submit="createCampaign" class="space-y-4">
                    {{ $this->campaignForm }}
                    <x-filament::button type="submit" color="primary" class="w-full">Create</x-filament::button>
                </form>
            </x-filament::section>

            <x-filament::section heading="Import Leads">
                <form wire:submit="importLeads" class="space-y-4">
                    {{ $this->leadImportForm }}
                    <x-filament::button type="submit" color="success" class="w-full">Import</x-filament::button>
                </form>
            </x-filament::section>
        </div>

        <!-- Campaign Management Table -->
        <x-filament::section heading="Campaigns" icon="heroicon-m-cog-6-tooth">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200/20">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Leads</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Pending</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Active</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/20">
                        @forelse ($this->getCampaigns() as $campaign)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 font-medium">{{ $campaign->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($campaign->status->value === 'running') bg-emerald-100 text-emerald-800
                                        @elseif($campaign->status->value === 'paused') bg-amber-100 text-amber-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $campaign->status->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-medium">{{ $campaign->leads_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-amber-700">{{ $campaign->pending_leads_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-center text-emerald-700">{{ $campaign->active_attempts_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-1">
                                        @if($campaign->status->value !== 'running')
                                            <x-filament::button size="xs" color="success" wire:click="startCampaign({{ $campaign->id }})" icon="heroicon-m-play">
                                            </x-filament::button>
                                        @else
                                            <x-filament::button size="xs" color="warning" wire:click="pauseCampaign({{ $campaign->id }})" icon="heroicon-m-pause">
                                            </x-filament::button>
                                        @endif
                                        <x-filament::button size="xs" color="danger" wire:click="stopCampaign({{ $campaign->id }})" icon="heroicon-m-stop">
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No campaigns yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Recent Call Attempts -->
        <x-filament::section heading="Recent Calls" icon="heroicon-m-phone">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200/20">
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Campaign</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Phone</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/20">
                        @forelse ($this->getRecentAttempts() as $attempt)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3">{{ $attempt->campaign?->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono">{{ $attempt->lead?->phone_number ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($attempt->status->value === 'completed') bg-emerald-100 text-emerald-800
                                        @elseif($attempt->status->value === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($attempt->status->value === 'failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $attempt->status->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $attempt->duration_seconds ? $attempt->duration_seconds . 's' : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No calls yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>

