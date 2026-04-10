{{-- Manus modal rendered outside Topbar to prevent Livewire entangle conflicts --}}
<x-filament::modal id="manus-calendar-modal" class="manus-modal fi-modal-slide-over-left" slide-over width="lg">
    <x-slot name="heading">
    </x-slot>

    @livewire('manus-icon-modal', [], key('manus-icon-modal-' . Auth::id()))
</x-filament::modal>
