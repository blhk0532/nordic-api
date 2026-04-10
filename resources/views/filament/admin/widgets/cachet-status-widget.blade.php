<x-filament-widgets::widget class="fi-cachet-status-widget">
    <x-filament::section>
        <x-slot name="headerEnd">
            <x-filament::link
                href="{{ route('cachet.status-page') }}"
                target="_blank"
                color="gray"
                size="sm"
                icon="heroicon-m-arrow-top-right-on-square"
                icon-position="after"
            >
                Open full page
            </x-filament::link>
        </x-slot>

        <iframe
            src="{{ route('cachet.status-page') }}"
            class="w-full rounded-lg border-0"
            style="height: 600px; min-height: 400px;"
            loading="lazy"
        ></iframe>
    </x-filament::section>
</x-filament-widgets::widget>
