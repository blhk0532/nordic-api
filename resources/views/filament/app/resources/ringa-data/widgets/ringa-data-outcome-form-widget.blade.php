<x-filament-widgets::widget class="h-full outcome-form-widget">
    <x-filament::section class="h-full">
        @php
            $recordId = $this->recordId ?? $this->record?->id;
            $tenant = filament()->getTenant()?->slug;
            $filamentTenant = filament()->getTenant();
        @endphp

        @if($recordId)
            <livewire:ringa-data.outcome-recorder :record-id="$recordId" :tenant="$tenant" class="h-full" />
        @else
            <div class="p-4 text-center text-gray-500">
                <div class="text-sm mb-2">No record selected</div>
                <div class="text-xs text-gray-400">Tenant: {{ $tenant ?? 'null' }} | Filament: {{ $filamentTenant?->slug ?? 'null' }}</div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
