<x-filament-panels::page>
    <div class="space-y-6" wire:poll.5s>
        @livewire(\App\Filament\Widgets\DialpadWidget::class)

        <div class="grid gap-6 lg:grid-cols-2">
            <x-filament::section heading="Create Campaign">
                <form wire:submit="createCampaign" class="space-y-4">
                    {{ $this->campaignForm }}

                    <x-filament::button type="submit" color="primary">
                        Create Campaign
                    </x-filament::button>
                </form>
            </x-filament::section>

            <x-filament::section heading="Import Leads">
                <form wire:submit="importLeads" class="space-y-4">
                    {{ $this->leadImportForm }}

                    <x-filament::button type="submit" color="success">
                        Import Leads
                    </x-filament::button>
                </form>
            </x-filament::section>
        </div>

        <x-filament::section heading="Campaign Monitor">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2 pr-3">Campaign</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 pr-3">Leads</th>
                            <th class="py-2 pr-3">Pending</th>
                            <th class="py-2 pr-3">Active</th>
                            <th class="py-2 pr-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getCampaigns() as $campaign)
                            <tr class="border-t border-gray-200/20">
                                <td class="py-2 pr-3">{{ $campaign->name }}</td>
                                <td class="py-2 pr-3">{{ $campaign->status->value }}</td>
                                <td class="py-2 pr-3">{{ $campaign->leads_count }}</td>
                                <td class="py-2 pr-3">{{ $campaign->pending_leads_count }}</td>
                                <td class="py-2 pr-3">{{ $campaign->active_attempts_count }}</td>
                                <td class="py-2 pr-3">
                                    <div class="flex flex-wrap gap-2">
                                        <x-filament::button size="xs" color="success" wire:click="startCampaign({{ $campaign->id }})">
                                            Start
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="warning" wire:click="pauseCampaign({{ $campaign->id }})">
                                            Pause
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:click="stopCampaign({{ $campaign->id }})">
                                            Stop
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="gray" wire:click="queueNow({{ $campaign->id }})">
                                            Queue Now
                                        </x-filament::button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section heading="Recent Attempts">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2 pr-3">ID</th>
                            <th class="py-2 pr-3">Campaign</th>
                            <th class="py-2 pr-3">Lead</th>
                            <th class="py-2 pr-3">Status</th>
                            <th class="py-2 pr-3">Disposition</th>
                            <th class="py-2 pr-3">Duration</th>
                            <th class="py-2 pr-3">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getRecentAttempts() as $attempt)
                            <tr class="border-t border-gray-200/20">
                                <td class="py-2 pr-3">#{{ $attempt->id }}</td>
                                <td class="py-2 pr-3">{{ $attempt->campaign?->name }}</td>
                                <td class="py-2 pr-3">{{ $attempt->lead?->phone_number }}</td>
                                <td class="py-2 pr-3">{{ $attempt->status->value }}</td>
                                <td class="py-2 pr-3">{{ $attempt->disposition ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $attempt->duration_seconds ?? '-' }}</td>
                                <td class="py-2 pr-3">{{ $attempt->updated_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
