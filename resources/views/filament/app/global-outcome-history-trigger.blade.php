<div>
    <button
        class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
        tooltip="Utfallshistorik"
        color="gray"
        size="lg"
        x-on:click.prevent="$dispatch('open-modal', { id: 'global-outcome-history' })"
    >
        <x-filament::icon
            icon="ri-timer-flash-line"
            class="fi-icon fi-size-lg"
        />
    </button>
</div>
@livewire(\App\Livewire\GlobalOutcomeHistory::class)
