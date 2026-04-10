@php
$isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$anderia = \Andreia\FilamentUiSwitcher\Support\UiPreferenceManager::get('ui.layout', 'sidebar');
@endphp
<div class="fi-no-database">
<div>
<div class="fi-modal-trigger">
<button
    color="gray"
    icon-size="lg"
    @if($anderia === 'sidebar-no-topbar')
    class="fi-sidebar-database-notifications-btn"
    @else
    class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
    @endif
    wire:click="$dispatch('open-modal', { id: 'calendar-modal' })"
>

        <x-filament::icon
            icon="heroicon-o-calendar-days"
            class="fi-icon fi-size-lg"
        />

          @if($anderia === 'sidebar-no-topbar')
    <span

            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"

        class="fi-sidebar-database-notifications-btn-label"
    >
    Kalender
    </span>
 @endif
</button>
 </div>
</div>
</div>

<x-filament::modal id="calendar-modal" class="calendar-modal" slide-over width="screen">
    <x-slot name="heading">
        Booking Calendar
    </x-slot>
    @livewire('calendar-icon-modal')
</x-filament::modal>
