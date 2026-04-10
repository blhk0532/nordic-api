<div class="full-width-chat-container">
    <div class="full-width-chat-sidebar">
        <livewire:filament-wirechat.chats :hideHeader="true" />
    </div>

    <main class="full-width-chat-main">
        <livewire:filament-wirechat.chat :conversation="$conversation->id" />
    </main>
</div>
