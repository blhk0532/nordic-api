{{-- User notes modal rendered outside Topbar to prevent Livewire entangle conflicts --}}
<x-filament::modal id="user-notes-modal" slide-over width="2xl" class="user-notes-modal">
    <x-slot name="heading">
        My Notes
    </x-slot>

    @livewire('user-notes', [], key('user-notes-' . Auth::id()))
</x-filament::modal>
