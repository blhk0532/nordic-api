<x-filament-widgets::widget>
    <x-filament::section heading="{{ $this->getHeading() }}" description="{{ $this->getDescription() }}">
        <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <iframe
                src="{{ $iframeUrl }}"
                style="width: 100%; height: {{ $iframeHeight }}; border: none;"
                loading="lazy"
                sandbox="allow-same-origin allow-scripts allow-popups allow-forms allow-presentation"
                allow="microphone; camera"
                title="External Application"
            ></iframe>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
