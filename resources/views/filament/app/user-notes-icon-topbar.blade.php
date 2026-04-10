@php

$isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$anderia = \Andreia\FilamentUiSwitcher\Support\UiPreferenceManager::get('ui.layout', 'sidebar');
$aSiderbar = $anderia === 'sidebar-no-topbar' ? true : false;
@endphp
<div class="fi-no-database" x-data>
<div>
<div class="fi-modal-trigger ml-3">
<button
    color="gray"
    icon="heroicon-c-information-circle"
    icon-size="xl"
    label="Kalender"
    @if($anderia === 'sidebar-no-topbar')
    class="fi-sidebar-database-notifications-btn"
    @else
    class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn fi-left-topbar-btn"
    @endif
     wire:click="$dispatch('open-modal', { id: 'manus-calendar-modal' })"
>

        <x-filament::icon
            icon="heroicon-o-clipboard-document"
            class="fi-icon fi-size-lg"
        />
@php
// dd($anderia);
@endphp

  @if($anderia === 'sidebar-no-topbar')





    <span

            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"

        class="fi-sidebar-database-notifications-btn-label"
    >
    Ateckning
    </span>
@endif

</button>
 </div>
</div>
</div>
