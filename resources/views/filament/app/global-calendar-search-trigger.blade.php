<div>
    <button
        class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
        tooltip="Kalender"
        color="gray"
        size="lg"
        x-on:click.prevent="$dispatch('open-modal', { id: 'global-calendar-search' })"
    >
        <x-filament::icon
            icon="heroicon-o-calendar-days"
            class="fi-icon fi-size-lg"
        />
    </button>

    @livewire(\App\Livewire\GlobalCalendarSearch::class)
</div>
