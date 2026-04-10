@php
    $user = filament()->auth()->user();
$cards =  $this->getCards();
@endphp
<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        @php
            $isOnline = $user->isOnline();
        @endphp
        {{ $isOnline ? 'Online' : 'Offline' }}
 {{ $cards }}
    </x-filament::section>
</x-filament-widgets::widget>
