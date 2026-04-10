<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Database Backup & Management</x-slot>
        <x-slot name="description">Create backups, export/import single tables, and manage your database.</x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Full Database --}}
            <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Full Database</h4>
                <div class="flex flex-col gap-2">
                    <button wire:click="backupDatabase" wire:confirm="Create a full database backup?" class="fi-btn fi-btn-primary fi-color-success gap-1.5 inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75 focus:ring-2 disabled:pointer-events-none disabled:opacity-75 bg-success-600 text-white shadow-sm hover:bg-success-500 focus:ring-success-500 dark:bg-success-500 dark:hover:bg-success-400 dark:focus:ring-success-400">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        Backup Database
                    </button>
                </div>
            </div>

            {{-- Export Table --}}
            <div class="p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-3">Export Table</h4>
                <div class="flex flex-col gap-2">
                    <select wire:model="exportTable" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select table...</option>
                         @foreach($this->getTableList() as $table)
                            <option value="{{ $table }}">{{ $table }}</option>
                        @endforeach
                    </select>
                    
                    @if($exportTable)
                        @php $info = $this->getTableInfo($exportTable); @endphp
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $info['rows'] }} rows, {{ $info['size_mb'] }} MB</p>
                    @endif

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="exportStructure" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        Include Structure
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="exportData" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        Include Data
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="exportIndexes" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        Include Indexes
                    </label>

                    <button wire:click="exportTableData" class="fi-btn fi-btn-primary fi-color-info gap-1.5 inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75 focus:ring-2 disabled:pointer-events-none disabled:opacity-75 bg-info-600 text-white shadow-sm hover:bg-info-500 focus:ring-info-500">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        Export Table
                    </button>
                </div>
            </div>

            {{-- Import Table --}}
            <div class="p-4 bg-green-50 rounded-lg dark:bg-green-900/20">
                <h4 class="text-sm font-medium text-green-700 dark:text-green-300 mb-3">Import Table (Safe)</h4>
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-green-600 dark:text-green-400 mb-2">Uses INSERT IGNORE - no data loss!</p>

                    {{ $this->getImportTableAction() }}
                </div>
            </div>

            {{-- Information --}}
            <div class="p-4 bg-purple-50 rounded-lg dark:bg-purple-900/20">
                <h4 class="text-sm font-medium text-purple-700 dark:text-purple-300 mb-3">Information</h4>
                <div class="flex flex-col gap-2">
                    <button wire:click="listTables" class="fi-btn fi-btn-primary fi-color-gray gap-1.5 inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium outline-none transition duration-75 focus:ring-2 disabled:pointer-events-none disabled:opacity-75 bg-gray-600 text-white shadow-sm hover:bg-gray-500 focus:ring-gray-500">
                        <x-heroicon-o-list-bullet class="w-5 h-5" />
                        List All Tables
                    </button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
