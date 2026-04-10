<div
    id="global-calendar-search"
    x-data="{ open: false }"
    x-show="open"
    x-on:open-modal.window="if ($event.detail.id === 'global-calendar-search') { open = true }"
    x-on:close-modal.window="if ($event.detail.id === 'global-calendar-search') { open = false }"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-hidden"
>
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 transition-opacity"
        x-on:click="open = false"
    ></div>
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-3xl sm:w-[800px] max-w-[500px] bg-white dark:bg-gray-800 shadow-xl overflow-hidden"
        style="max-width: 800px; height: 100vh;"
    >
        <div class="h-full calendar-search-modal-widget" style="min-height: 100%; overflow: hidden;">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Kalender
                    </h2>
                    <button
                        x-on:click="open = false"
                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-hidden" style="height: calc(100vh - 80px);">
                    @livewire(\App\Livewire\CalendarIconModal::class)
                </div>
            </div>
        </div>
    </div>
</div>
