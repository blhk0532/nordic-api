@php
    $unreadCount = auth()->user()?->getUnreadCount() ?? 0;
@endphp

{{-- Modal without teleport to ensure it's always in DOM --}}
<x-filament::modal
    id="chats-sidebar"
    slide-over
    width="md"
    close-button
>
    <x-slot name="heading">
        @if($unreadCount > 0)
            <x-filament::badge color="primary" size="xs">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </x-filament::badge>
        @endif
    </x-slot>

    <div class="chats-sidebar-content">
        @php
            $widgetValue = false;
        @endphp
        <livewire:filament-wirechat.chats :widget="$widgetValue" :hideHeader="true" />
    </div>
</x-filament::modal>
