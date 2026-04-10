<x-filament-panels::page>
    {{-- Always show header on chats page - adjust height to account for Filament page header and header height --}}
    <div class="w-full h-[calc(100vh-4rem)] flex rounded-lg overflow-hidden">
        {{-- Conversations list --}}
        <div class="relative h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] w-full md:w-[280px] lg:w-[300px] shrink-0 overflow-hidden flex-col">
            {{-- Pass hideHeader=false to always show the header --}}
            <livewire:filament-wirechat.chats :hideHeader="false" />
        </div>
        <main class="hidden md:flex h-full flex-1 bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)] relative overflow-hidden flex-col" style="contain:content">
            <div class="m-auto text-center justify-center flex gap-3 flex-col items-center">
                <h4 class="font-medium p-2 px-3 rounded-full font-semibold bg-[var(--wc-light-secondary)] dark:bg-[var(--wc-dark-secondary)] dark:text-white dark:font-normal">
                    @lang('filament-wirechat::pages.chat.messages.welcome')
                </h4>
            </div>
        </main>
    </div>

    {{-- Include modal component for new chat and other modals --}}
    <livewire:filament-wirechat.modal />
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
