<div 
    x-data="{ 
        hasConversation: true,
        updateFromUrl() {
            // Check if URL has a conversation ID (e.g., /chats/2)
            const path = window.location.pathname;
            const match = path.match(/\/chats\/(\d+)/);
            this.hasConversation = match !== null;
        }
    }"
    x-init="
        updateFromUrl();
        // Update when Livewire navigates (wire:navigate completes)
        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => updateFromUrl(), 50);
        });
        // Also listen for popstate (browser back/forward)
        window.addEventListener('popstate', () => {
            setTimeout(() => updateFromUrl(), 50);
        });
    "
    class="w-full flex min-h-full h-full rounded-lg"
>
    {{-- Sidebar: Conversations list - hidden on mobile --}}
    <div class="hidden md:grid bg-inherit border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] dark:bg-inherit relative w-full h-full md:w-[360px] lg:w-[400px] xl:w-[500px] shrink-0 overflow-y-auto">
        <livewire:wirechat.chats :panel="$panel" />
    </div>

    {{-- Main chat area - full width on mobile --}}
    <main class="flex w-full grow h-full min-h-min relative overflow-y-auto" style="contain:content">
        <livewire:wirechat.chat :panel="$panel" conversation="{{$this->conversation->id}}"/>
    </main>
</div>
