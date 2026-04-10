<x-filament-widgets::widget>
    <div id="app-wirechat-widget" class="w-full h-full min-h-[calc(100vh-4rem)] overflow-hidden">
        <div class="w-full h-full min-h-full flex rounded-lg">
            {{-- Sidebar: Conversations list - full width on mobile when no chat is open --}}
            <div x-persist="chats" class="relative w-full h-full border-r border-[var(--wc-light-border)] dark:border-[var(--wc-dark-border)] md:w-[360px] lg:w-[400px] xl:w-[500px] shrink-0 overflow-y-auto">
                <livewire:filament-wirechat.chats :panel="\Filament\Facades\Filament::getCurrentPanel()?->getId()" />
            </div>

            {{-- Welcome message - hidden on mobile, shown on desktop --}}
            <main class="hidden md:grid h-full min-h-full w-full bg-[var(--wc-light-primary)] dark:bg-[var(--wc-dark-primary)] h-full relative overflow-y-auto" style="contain:content">
                <div class="m-auto text-center justify-center flex gap-3 flex-col items-center col-span-12">
                    <h4 class="font-medium p-2 px-3 rounded-full font-semibold bg-[var(--wc-light-secondary)] dark:bg-[var(--wc-dark-secondary)] dark:text-white dark:font-normal">@lang('filament-wirechat::pages.chat.messages.welcome')</h4>
                </div>
            </main>
        </div>
    </div>

    <style>
        .fi-main.fi-width-full { padding: 0rem !important; background: #18181b;}
        .fi-page-header-main-ctn { padding: 0rem !important; }
    .fi-sc-component{
        border-left: 1px solid #4f4f56ad;
    }
    div.fi-section .fi-loading-section {
        min-height: 96vh!important;
        border-radius: 0px!important;
    }
        /* Hide empty schema component elements that create gaps */
        [x-data*="filamentSchemaComponent"]:empty { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; min-height: 0 !important; }
        [x-data*="path: ''"]:empty { display: none !important; height: 0 !important; margin: 0 !important; padding: 0 !important; }

        /* Remove gap between widgets on dashboard - target footer widgets container */
        .fi-page-footer-widgets > div:last-child, .fi-page-footer-widgets > [wire\:id*="wirechat-widget"] { margin-top: -1.5rem !important; }
    </style>

    <script>
        (function() {
            function removeEmptySchemaComponents() {
                document.querySelectorAll('[x-data*="filamentSchemaComponent"][x-data*="path: \'\'"]').forEach((el) => {
                    if (!el.textContent.trim() && el.children.length === 0) { el.remove(); }
                });
            }

            if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', removeEmptySchemaComponents); } else { removeEmptySchemaComponents(); }

            document.addEventListener('livewire:init', () => { Livewire.hook('morph', () => { setTimeout(removeEmptySchemaComponents, 50); }); });
        })();
    </script>
</x-filament-widgets::widget>
