<div class="flex flex-col gap-4">
    @foreach ($stackedWidgets as $widget)
        @livewire($widget)
    @endforeach
</div>
