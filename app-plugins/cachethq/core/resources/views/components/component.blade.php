{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_COMPONENTS_BEFORE) }}
<li class="px-4 py-3">
    <div class="flex items-center justify-between">
        <div class="flex flex-col grow gap-y-1">
            <div class="flex justify-between items-center gap-1.5">
                <div class="flex gap-x-1">
                    <div class="font-semibold leading-6">{{ $component->name }}</div>

                    <div x-data="{ open: false }" class="relative flex items-center">
                        <button
                            type="button"
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            @focus="open = true"
                            @blur="open = false"
                            @click.prevent="open = ! open"
                            class="inline-flex items-center"
                            aria-label="{{ __('cachet::component.last_updated', ['timestamp' => $component->name]) }}"
                        >
                            <x-heroicon-o-question-mark-circle class="size-4 text-zinc-500 dark:text-zinc-300" />
                        </button>
                        <div
                            x-cloak
                            x-show="open"
                            x-transition.opacity
                            @mouseenter="open = true"
                            @mouseleave="open = false"
                            class="absolute left-6 top-1/2 z-20 -translate-y-1/2 whitespace-nowrap rounded-sm bg-zinc-900 px-2 py-1 text-xs font-medium text-zinc-100 drop-shadow-sm dark:bg-zinc-200 dark:text-zinc-800"
                        >
                            <span class="pointer-events-none absolute -left-1.5 top-1/2 size-4 -translate-y-1/2 rotate-45 bg-zinc-900 dark:bg-zinc-200"></span>
                            <p class="relative">{{ __('cachet::component.last_updated', ['timestamp' => $component->name]) }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    @if ($component->incidents_count > 0)
                        <a href="{{ route('cachet.status-page.incident', [$component->incidents->first()]) }}">
                            <x-cachet::badge :status="$component->latest_status" />
                        </a>
                    @else
                        <x-cachet::badge :status="$status" />
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-y-1 text-xs text-zinc-500 dark:text-zinc-300">
                @if($component->description)
                @php
                    $componentDescription = (string) $component->description;
                    $hasHtmlTags = preg_match('/<[^>]+>/', $componentDescription) === 1;
                    $renderedDescription = $hasHtmlTags
                        ? $componentDescription
                        : \Illuminate\Support\Str::markdown($componentDescription, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]);
                @endphp
                <div class="prose-sm md:prose prose-zinc max-w-none dark:prose-invert prose-a:text-accent-content prose-a:underline prose-p:leading-normal">{!! $renderedDescription !!}</div>
                @endif
                @if($component->link)
                <a href="{{ $component->link }}" class="text-zinc-700 underline dark:text-zinc-300" target="_blank" rel="nofollow noopener">{{ __('cachet::component.view_details') }}</a>
                @endif
            </div>
        </div>
    </div>
</li>
{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_AFTER) }}
