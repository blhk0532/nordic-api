<x-filament-widgets::widget class="fi-filament-kanban-widget">
    <x-filament::section>
<div class="fi-wi-stats-overview-grid flex gap-4 overflow-x-auto pb-4">
    @foreach($columns as $label => $outcome)
        <div class="min-w-[280px] flex-1 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
            <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ isset($records[$outcome]) ? count($records[$outcome]) : 0 }} poster
                </p>
            </div>
            <div class="p-2 max-h-[400px] overflow-y-auto space-y-2">
                @forelse(isset($records[$outcome]) ? $records[$outcome] : [] as $record)
                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow cursor-pointer">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $record->first_name }} {{ $record->last_name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $record->phone ?? 'No phone' }}
                                </p>
                            </div>
                            @if($record->company)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $record->company }}
                                </span>
                            @endif
                        </div>
                        @if($record->notes)
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                                {{ $record->notes }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">
                        Inga poster
                    </p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
    </x-filament::section>
</x-filament-widgets::widget>
