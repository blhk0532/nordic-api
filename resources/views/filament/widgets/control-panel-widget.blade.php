@php
    $user = filament()->auth()->user();
@endphp
<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        @php
            $isOnline = $user->isOnline();
        @endphp
        {{ $isOnline ? 'Online' : 'Offline' }}

    </x-filament::section>
</x-filament-widgets::widget>
