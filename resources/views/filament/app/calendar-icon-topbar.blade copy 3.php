@php
$isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$anderia = \Andreia\FilamentUiSwitcher\Support\UiPreferenceManager::get('ui.layout', 'sidebar');
$buttonClass = $anderia === 'sidebar-no-topbar' ? 'fi-sidebar-database-notifications-btn' : 'fi-icon-btn fi-size-md fi-topbar-database-notifications-btn';
@endphp
<div class="fi-no-database" x-data>
<div>
<div class="fi-modal-trigger">
<x-filament::button
    color="gray"
    icon="heroicon-o-calendar-days"
    icon-size="lg"
    label="{{ __('Calendar') }}"
    x-show="$store.sidebar.isOpen"
    class="{{ $buttonClass }}"
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

        class="fi-sidebar-database-notifications-btn-label hidden"
    >
    Kalender
    </span>
 @endif
</x-filament::button>
 </div>
</div>
</div>

<x-filament::modal id="calendar-modal" class="calendar-modal fi-modal-slide-over-left" slide-over width="3xl">
    <x-slot name="heading">
        Booking Calendar
    </x-slot>
     @livewire('calendar-icon-modal')

</x-filament::modal>
