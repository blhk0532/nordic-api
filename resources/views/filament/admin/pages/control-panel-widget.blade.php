@php
$cards =  $this->getCards();
@endphp


<div class="flex flex-col gap-4">
    @foreach ($cards as $card)
        @livewire($card)
    @endforeach
</div>
