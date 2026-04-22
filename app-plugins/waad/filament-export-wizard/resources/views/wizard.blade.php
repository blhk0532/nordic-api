<div>
    <div class="flex flex-col gap-6">
        <!-- Steps Header -->
        <div class="flex items-center justify-between border-b pb-4">
            <div class="flex gap-4">
                <div @class([
                    'flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
                    'bg-primary-500 text-white' => $step === 1,
                    'bg-gray-100 text-gray-500' => $step !== 1,
                ])>
                    <span class="w-6 h-6 flex items-center justify-center rounded-full bg-white/20">1</span>
                    Selection
                </div>
                <div @class([
                    'flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
                    'bg-primary-500 text-white' => $step === 2,
                    'bg-gray-100 text-gray-500' => $step !== 2,
                ])>
                    <span class="w-6 h-6 flex items-center justify-center rounded-full bg-white/20">2</span>
                    Format
                </div>
                <div @class([
                    'flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
                    'bg-primary-500 text-white' => $step === 3,
                    'bg-gray-100 text-gray-500' => $step !== 3,
                ])>
                    <span class="w-6 h-6 flex items-center justify-center rounded-full bg-white/20">3</span>
                    Finish
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="min-h-[300px]">
            @if($step === 1)
                <div>
                    <h3 class="text-lg font-medium mb-4">Select Columns to Export</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($columnOptions as $column)
                            <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" wire:model="selectedColumns" value="{{ $column }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-700 font-medium">{{ str($column)->headline() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @elseif($step === 2)
                <div>
                    <h3 class="text-lg font-medium mb-4">Export Format</h3>
                    <div class="flex gap-4">
                        <label class="flex-1 flex flex-col items-center gap-3 p-6 border-2 rounded-xl cursor-pointer transition @if($format === 'csv') border-primary-500 bg-primary-50 @else border-gray-200 hover:border-gray-300 @endif">
                            <input type="radio" wire:model="format" value="csv" class="sr-only">
                            <x-heroicon-o-document-text class="w-12 h-12 text-gray-400 @if($format === 'csv') text-primary-500 @endif" />
                            <span class="font-bold">CSV</span>
                            <span class="text-xs text-gray-500 text-center text-pretty">Standard comma-separated values file</span>
                        </label>
                        <label class="flex-1 flex flex-col items-center gap-3 p-6 border-2 rounded-xl cursor-pointer transition @if($format === 'xlsx') border-primary-500 bg-primary-50 @else border-gray-200 hover:border-gray-300 @endif">
                            <input type="radio" wire:model="format" value="xlsx" class="sr-only">
                            <x-heroicon-o-table-cells class="w-12 h-12 text-gray-400 @if($format === 'xlsx') text-primary-500 @endif" />
                            <span class="font-bold">Excel</span>
                            <span class="text-xs text-gray-500 text-center text-pretty">Microsoft Excel Spreadsheet (.xlsx)</span>
                        </label>
                    </div>
                </div>
            @elseif($step === 3)
                <div class="flex flex-col items-center justify-center py-10">
                    <div class="w-20 h-20 bg-success-100 text-success-600 rounded-full flex items-center justify-center mb-6">
                        <x-heroicon-o-check class="w-12 h-12" />
                    </div>
                    <h3 class="text-2xl font-bold mb-2">Ready to Export</h3>
                    <p class="text-gray-500 text-center max-w-md mx-auto">
                        Your export file will contain {{ count($selectedColumns) ?: 'all' }} columns in {{ strtoupper($format) }} format.
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between border-t pt-6">
            @if($step > 1)
                <button type="button" wire:click="previousStep" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Previous
                </button>
            @else
                <div></div>
            @endif

            <div class="flex gap-3">
                @if($step < 3)
                    <button type="button" wire:click="nextStep" class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                        Next
                    </button>
                @else
                    <button type="button" wire:click="export" class="inline-flex items-center px-8 py-2 text-sm font-medium text-white bg-success-600 rounded-lg hover:bg-success-700">
                        Download File
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
