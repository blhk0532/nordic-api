
@php
$isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$anderia = \Andreia\FilamentUiSwitcher\Support\UiPreferenceManager::get('ui.layout', 'sidebar');
@endphp
<div class="fi-no-database">
<div>
<div class="fi-modal-trigger">
<button
    icon="heroicon-o-chat-bubble-left-right"
    @if($anderia === 'sidebar-no-topbar')
    class="fi-sidebar-database-notifications-btn"
    @else
    class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
    @endif
    tooltip="Chats"
    color="gray"
    size="lg"

    x-on:click.prevent="$dispatch('open-modal', { id: 'chats-sidebar' })"
    wire:key="chats-icon-button-{{ $this->unreadCount }}"
>

        <x-filament::icon
            icon="heroicon-o-chat-bubble-left-right"
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
    Meddelande
    </span>
 @endif

@php
// dd($anderia);
@endphp
    @if($this->unreadCount > 0)
        <x-slot name="badge fi-badge fi-size-xs fi-color fi-color-primary fi-text-color-700 dark:fi-text-color-400">
            {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
        </x-slot>
    @endif


</button>
</div>
</div>
</div>
