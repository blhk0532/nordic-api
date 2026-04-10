{{-- Full-width chats page matching wirechat preview - covers entire window --}}
@php
    $wirechatPanelId = 'wirechat';
@endphp
<div class="h-screen w-full overflow-hidden">
    <div class="flex h-full w-full overflow-hidden bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]">
        {{-- Left sidebar: Conversations list --}}
        <div class="relative h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] w-full md:w-[280px] lg:w-[300px] shrink-0 overflow-hidden flex-col bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]">
            <livewire:filament-wirechat.chats :panel="$wirechatPanelId" />
        </div>

        {{-- Right side: Welcome message --}}
        <main class="hidden md:flex h-full flex-1 bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)] relative overflow-hidden flex-col" style="contain:content">
            <div class="m-auto text-center justify-center flex gap-3 flex-col items-center">
                <h4 class="font-medium p-2 px-3 rounded-full font-semibold bg-[var(--wc-light-secondary)] dark:bg-[var(--wc-dark-secondary)] text-gray-900 dark:text-white dark:font-normal">
                    @lang('filament-wirechat::pages.chat.messages.welcome')
                </h4>
            </div>
        </main>
    </div>

    {{-- Include modal component for new chat and other modals --}}
    <livewire:filament-wirechat.modal :panel="$wirechatPanelId" />
    <livewire:filament-wirechat.chat.drawer :panel="$wirechatPanelId" />
</div>
