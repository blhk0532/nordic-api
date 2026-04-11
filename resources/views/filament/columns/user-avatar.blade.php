<div class="flex items-center gap-3">
    @if($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="User avatar" class="w-8 h-8 rounded-full">
    @endif
    <div class="flex flex-col">
        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $name }}</span>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $email }}</span>
    </div>
</div>