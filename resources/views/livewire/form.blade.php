<div class="w-full">
    <form wire:submit="submit" class="space-y-4">
        {{ $this->form }}

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
        </div>
    </form>
</div>
