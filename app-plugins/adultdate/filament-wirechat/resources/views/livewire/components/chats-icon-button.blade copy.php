<style>
.fi-sidebar-database-notifications-btn{
justify-content: center;
    align-items: center;
    column-gap: calc(var(--spacing)*3);
    border-radius: var(--radius-lg);
    width: 100%;
    padding: calc(var(--spacing)*2);
    text-align: start;
    --tw-outline-style: none;
    outline-style: none;
    display: flex;
    position: relative;
    padding: 8px;
}

</style>

<button
    icon="heroicon-o-chat-bubble-left-right"
    class="fi-sidebar-database-notifications-btn"
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

    <span

            x-show="$store.sidebar.isOpen"
            x-transition:enter="fi-transition-enter"
            x-transition:enter-start="fi-transition-enter-start"
            x-transition:enter-end="fi-transition-enter-end"

        class="fi-sidebar-database-notifications-btn-label"
    >
       Wirechat
    </span>


    @if($this->unreadCount > 0)
        <x-slot name="badge fi-badge fi-size-xs fi-color fi-color-primary fi-text-color-700 dark:fi-text-color-400">
            {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
        </x-slot>
    @endif


</button>
