<div 
    x-data="{ 
        hasConversation: false,
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
    class="w-full h-full min-h-full flex rounded-lg"
>
    <div 
        :class="hasConversation ? 'hidden md:flex' : 'flex'"
        class="relative w-full h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] md:w-[360px] lg:w-[400px] xl:w-[500px] shrink-0 overflow-y-auto"
    >
      <livewire:wirechat.chats :panel="$panel" />
    </div>
    <main 
        :class="hasConversation ? 'flex' : 'hidden md:grid'"
        class="h-full min-h-full w-full bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)] relative overflow-y-auto"  
        style="contain:content"
    >
        <template x-if="!hasConversation">
            <div class="m-auto text-center justify-center flex gap-3 flex-col items-center col-span-12">
                <h4 class="font-medium p-2 px-3 rounded-full font-semibold bg-[var(--wc-light-secondary)] dark:bg-[var(--wc-dark-secondary)] dark:text-white dark:font-normal">
                    @lang('wirechat::pages.chat.messages.welcome')
                </h4>
            </div>
        </template>
    </main>
</div>
