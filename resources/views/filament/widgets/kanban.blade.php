<div class="w-full flex-col">
    <div @class([
        'fi-wi-kanban',
        'col-span-full' => $this->getColumnSpan() === 'full',
    ])>
        @livewire($kanbanWidget[0])
    </div>
</div>
