<x-filament-panels::page>
    {{-- Always show header on chat page - same height as chats page --}}
    <div class="w-full h-[calc(100vh-4rem)] flex rounded-lg overflow-hidden" wire:key="chat-page-{{ $conversation->id }}">
        {{-- Conversations list - hidden on mobile --}}
        <div class="hidden md:flex relative h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] w-[280px] lg:w-[300px] shrink-0 overflow-hidden flex-col">
            {{-- Always show header on chat page --}}
            <livewire:filament-wirechat.chats :hideHeader="false" />
        </div>

        {{-- Chat window - full width on mobile, flex-1 on desktop --}}
        <main class="flex flex-1 h-full relative overflow-hidden flex-col bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)]" style="contain:content; min-width: 0; max-width: 100%;">
            <livewire:filament-wirechat.chat :conversation="$conversation->id" wire:key="chat-component-{{ $conversation->id }}" />
        </main>
    </div>

    {{-- Include modal and drawer components --}}
    <livewire:filament-wirechat.modal />
    <livewire:filament-wirechat.chat.drawer />
    <style>
.fi-page-main{max-height: calc(100vh - 4rem) !important;}
.fi-page-header-main-ctn{max-height: calc(100vh - 4rem) !important;padding: 0px !important;margin: 0px !important;}
.fi-page{max-height: calc(100vh - 4rem) !important;}
.fi-page-content{max-height: calc(100vh - 4rem) !important;}
.fi-main.fi-width-7xl{max-height: calc(100vh - 4rem) !important;}
.fi-main.fi-width-7xl{padding: 0px !important;margin: 0px !important;width:100% !important;min-width:100% !important;}
.fi-main.fi-width-7xl{max-height: calc(100vh - 4rem) !important;}
.fi-main.fi-width-7xl{max-height: calc(100vh - 4rem) !important;}
aside.fi-sidebar.fi-main-sidebar.fi-sidebar-open{display: none !important;}
.fi-main.fi-width-full{padding:0px !important;margin:0px !important;width:100% !important;min-width:100% !important;}
.fi-page-header-main-ctn{padding: 0px !important;margin: 0px !important;  }
 </style>
</x-filament-panels::page>
