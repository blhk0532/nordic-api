@php
    use Filament\Support\Enums\IconSize;
@endphp

<div>
    @livewire('user-notes-slide-over', [], key('user-notes-slide-over-' . Auth::id()))

    <button
        type="button"
        onclick="Livewire.dispatch('open-user-notes-slide-over')"
        class="flex items-center justify-center"
        title="{{ __('') }}"
    >
        <x-filament::icon
            icon="heroicon-o-document-text"
            :size="IconSize::Large"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        />
    </button>
</div>
