@include('cachet::filament.widgets.partials.cachet-widget-assets')

<div class="bg-accent-background text-zinc-700 dark:text-zinc-300">
    {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_BEFORE) }}

    <section class="fi-section" style="padding: 0px;" id="status-overview-page">
        <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-gray dark:bg-gray/5">
                <div class="flex w-full flex-col space-y-6">

                    <x-cachet::about />

                </div>
            </div>
        </div>
    </section>

    {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_AFTER) }}
</div>
