<div>
    <button
        class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn mr-1"
        tooltip="Ringa Data"
        color="gray"
        size="lg"
        x-on:click.prevent="$dispatch('open-modal', { id: 'global-ringa-data-search' })"
    >
        <x-filament::icon
            icon="ri-timer-flash-line"
            class="fi-icon fi-size-lg"
        />
    </button>

    @livewire(\App\Livewire\GlobalRingaDataSearch::class)
</div>
