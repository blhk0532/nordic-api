{{-- Full-width chat page matching wirechat preview - covers entire window --}}
@php
    $wirechatPanelId = 'wirechat';
@endphp
<div class="h-screen w-full overflow-hidden" wire:key="chat-page-{{ $conversation->id }}">
    <div class="flex h-full w-full overflow-hidden bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]">
        {{-- Left sidebar: Conversations list - hidden on mobile --}}
        <div class="hidden md:flex relative h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] w-[280px] lg:w-[300px] shrink-0 overflow-hidden flex-col bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]">
            <livewire:filament-wirechat.chats :panel="$wirechatPanelId" />
        </div>

        {{-- Right side: Chat conversation - full width on mobile --}}
        <main class="flex flex-1 h-full relative overflow-hidden flex-col bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]" style="contain:content">
            <livewire:filament-wirechat.chat :panel="$wirechatPanelId" conversation="{{ $conversation->id }}" wire:key="chat-{{ $conversation->id }}" />
        </main>
    </div>

    {{-- Include modal and drawer components --}}
    <livewire:filament-wirechat.modal :panel="$wirechatPanelId" />
    <livewire:filament-wirechat.chat.drawer :panel="$wirechatPanelId" />
</div>
