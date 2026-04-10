<x-filament-panels::page>
    @php
        $stats = \App\Models\SwedenPostnummer::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN personer > 0 THEN 1 ELSE 0 END) as with_personer,
            COUNT(latitude) as with_coordinates,
            SUM(personer) as total_personer
        ')->first();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4">
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-sm text-gray-500">Total Postnummer</div>
            <div class="text-2xl font-bold">{{ number_format($stats->total) }}</div>
        </div>
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-sm text-gray-500">With Personer</div>
            <div class="text-2xl font-bold">{{ number_format($stats->with_personer) }}</div>
        </div>
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-sm text-gray-500">With Coordinates</div>
            <div class="text-2xl font-bold">{{ number_format($stats->with_coordinates) }}</div>
        </div>
        <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
            <div class="text-sm text-gray-500">Total Personer</div>
            <div class="text-2xl font-bold">{{ number_format($stats->total_personer ?? 0) }}</div>
        </div>
    </div>


</x-filament-panels::page>