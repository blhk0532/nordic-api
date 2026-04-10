<x-filament-widgets::widget>
    <x-filament::section>
        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
            {!! $supportingText !!}
        </p>
        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
            {!! $supportingDate !!}
        </p>
        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400 font-semibold">
            {{ __('cachet::cachet.support.work_in_progress_text') }}
        </p>

        {{ $supportingHeading }}
        {{ $this->form }}
    </x-filament::section>
</x-filament-widgets::widget>
