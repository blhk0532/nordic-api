<div
    id="global-ringa-data-search"
    x-data="{
        open: false,
        filter: 'all',
        init() {
            window.addEventListener('open-modal', (event) => {
                if (event.detail.id === 'global-ringa-data-search') {
                    this.open = true;
                }
            });
            window.addEventListener('close-modal', (event) => {
                if (event.detail.id === 'global-ringa-data-search') {
                    this.open = false;
                }
            });
        }
    }"
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-hidden"
>
<style>

</style>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm"
        x-on:click="open = false"
    ></div>
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-8"
        class="fixed inset-y-0 right-0 z-50 w-full bg-white dark:bg-gray-900 shadow-2xl overflow-hidden flex flex-col"
        style="height: 100vh; max-width: 640px;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-600 to-primary-500">
            <div class="flex items-center gap-3">
                <x-filament::icon icon="ri-timer-flash-line" class="w-5 h-5 text-white" />
                <h2 class="text-base font-semibold text-white">
                    Ring Tillbaka - Återkomstlista
                </h2>
            </div>
            <button
                x-on:click="open = false"
                class="p-1.5 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
                <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
            </button>
        </div>

        <div class="px-0 py-0 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hidden">
            <select
                x-model="filter"
                x-on:change="$dispatch('update-ringa-filter', filter)"
                class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 py-0 text-sm focus:border-primary-500 focus:ring-primary-500"
            >
                <option value="all">Alla</option>
                <option value="aterkom">Återkom</option>
                <option value="offert">Offert</option>
                <option value="kontakt">Kontakt</option>
                <option value="historik">Historik</option>
            </select>
        </div>
<style>
</style>
        <div class="flex-1 overflow-hidden" style="height: calc(100vh - 280px);">
            <div class="h-full overflow-y-auto ringa-tillbaka-modal-content">
                @livewire(\App\Filament\App\Resources\RingaData\Widgets\AterkomRingTableWidget::class)
            </div>
        </div>
    </div>
</div>
