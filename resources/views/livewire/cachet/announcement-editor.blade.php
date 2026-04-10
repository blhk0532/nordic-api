<div class="space-y-4">
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ $editingId ? 'Edit' : 'Create' }} announcements for the status page.
    </p>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title *</label>
        <input 
            type="text" 
            wire:model="title"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800"
            placeholder="Enter title"
        >
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content</label>
        <textarea 
            wire:model="content"
            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 min-h-[100px]"
            placeholder="Enter content"
        ></textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
            <select wire:model="priority" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Technician</label>
            <select wire:model="tekniker_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                <option value="">Select technician</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Component</label>
            <select wire:model="component_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                <option value="">Select component</option>
                @foreach($components as $component)
                    <option value="{{ $component->id }}">{{ $component->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
            <input type="datetime-local" wire:model="starts_at" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
        <input type="datetime-local" wire:model="ends_at" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
    </div>

    <div class="flex justify-end">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
        >
            {{ $editingId ? 'Update' : 'Create' }} announcement
        </button>
    </div>
</div>
