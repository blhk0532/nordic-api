<div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4" x-data="{ open: true }">
    <template x-if="open">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <!-- Icon -->
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-blue-800">
                    @foreach($announcements as $announcement)
                        <div class="mb-2 last:mb-0">
                            <strong>{{ $announcement->title }}</strong>
                            <p class="text-sm text-blue-600 mt-1">{{ $announcement->content }}</p>
                            @if ($announcement->starts_at || $announcement->ends_at)
                                <span class="text-xs text-blue-500">
                                    @if ($announcement->starts_at)
                                        From: {{ $announcement->starts_at->format('M j, Y g:i A') }}
                                    @endif
                                    @if ($announcement->ends_at)
                                        @if ($announcement->starts_at)
                                            to
                                        @endif
                                        Until: {{ $announcement->ends_at->format('M j, Y g:i A') }}
                                    @endif
                                </span>
                            @endif
                        </div>
                    @endforeach
                </p>
                <div class="mt-2 flex justify-end">
                    <button @click="open = false"
                            class="text-sm text-blue-600 hover:text-blue-800">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>