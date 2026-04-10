@props(['panels' => []])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach ($panels as $panel)
        <a
            href="{{ $panel->getUrl() }}"
            class="block p-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >
            <div class="flex items-center space-x-3">
                @if ($panel->getBrandLogo())
                    <img src="{{ $panel->getBrandLogo() }}" alt="{{ $panel->getBrandName() }}" class="w-8 h-8">
                @else
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded flex items-center justify-center">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ substr($panel->getBrandName(), 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $panel->getBrandName() }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $panel->getPath() }}</p>
                </div>
            </div>
        </a>
    @endforeach
</div>