{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_ABOUT_BEFORE) }}
                 @php
                    $componentDescription = (string) $title;
                    $hasHtmlTags = preg_match('/<[^>]+>/', $componentDescription) === 1;
                    $renderedDescription = $hasHtmlTags
                        ? $componentDescription
                        : \Illuminate\Support\Str::markdown($componentDescription, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]);
                @endphp
@if ($about !== '')
<div>
    <h1 class="text-sm font-bold">
        {{ now()->format('l j F Y') }}
    </h1>
@if ($title !== 'Nordic Digital - Status')
    <div class="prose-sm md:prose prose-zinc dark:prose-invert prose-a:text-accent-content prose-a:underline">
        {!! $renderedDescription  !!}
    </div>
@endif
    <div class="prose-sm md:prose prose-zinc dark:prose-invert prose-a:text-accent-content prose-a:underline">
        {!! $about !!}
    </div>
</div>
@endif

{{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_ABOUT_AFTER) }}
