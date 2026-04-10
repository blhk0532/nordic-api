{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_SCHEDULES_BEFORE) }}
<li class="p-4" x-data="{ timestamp: new Date(@js($schedule->scheduled_at)) }">
    <div class="flex flex-col-reverse items-start justify-between gap-4 md:flex-row md:items-center">
        <div class="flex items-start gap-2.5 w-full">
            <div class="flex-1">
                <div class="flex flex-col sm:flex-row justify-between gap-2 flex-col-reverse items-start relative">
                    <div class="flex flex-col flex-1">
                        <div class="flex items-center gap-x-1">
                            <h3 class="max-w-full text-base font-semibold break-words sm:text-xl">
                                {{ $schedule->name }}
                            </h3>
                        </div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 hidden">
                            {{ $schedule->scheduled_at->diffForHumans() }} — <time datetime="{{ $schedule->scheduled_at->toW3cString()}}" x-text="timestamp.toLocaleString(@if($appSettings->timezone !== '-')undefined, {timeZone: '{{$appSettings->timezone}}'}@endif )"></time>
                        </span>
                        @if ($schedule->components->isNotEmpty())
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ __('@') }} {{ $schedule->components->pluck('name')->join(', ', ' and ') }}
                        </div>
                        @endif
                    </div>

                    <div class="flex justify-end sm:justify-end w-full md:w-auto">
                        <div class="absolute right-0 bottom-0 top-1 mr-2">
                        <x-cachet::badge :status="$schedule->status" />
                            </div>
                    </div>
                </div>

                <div style="min-width:100%" class="prose-sm md:prose prose-zinc dark:prose-invert prose-a:text-accent-content prose-a:underline prose-p:leading-normal max-w-full">{!! $schedule->formattedMessage() !!}</div>
            </div>
        </div>
    </div>

    <div class="relative">
        <div class="flex flex-col divide-y dark:divide-zinc-700">
            @foreach ($schedule->updates as $update)
            <div class="relative py-4" x-data="{ timestamp: new Date(@js($update->created_at)) }">
                <span class="text-xs text-zinc-500 dark:text-zinc-400 hidden">
                    {{ $update->created_at->diffForHumans() }} — <time datetime="{{ $update->created_at->toW3cString() }}" x-text="timestamp.toLocaleString(@if($appSettings->timezone !== '-')undefined, {timeZone: '{{$appSettings->timezone}}'}@endif )"></time>
                </span>
                <div style="min-width:100%" class="prose-sm md:prose prose-zinc dark:prose-invert prose-a:text-accent-content prose-a:underline prose-p:leading-normal max-w-full">{!! $update->formattedMessage() !!}</div>
            </div>
            @endforeach
        </div>
    </div>
</li>
{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_SCHEDULES_AFTER) }}
