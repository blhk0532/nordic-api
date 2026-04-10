 @include('cachet::filament.widgets.partials.cachet-widget-assets')
<x-filament::widget class="overflow-hidden" id="status-groups-widget">
<div class="text-zinc-700 dark:text-zinc-300 w-full">
    @if ($componentGroups->isNotEmpty())
        {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_BEFORE) }}

        <section class="fi-section" style="padding: 0px;" id="status-groups-widget-section">
            <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-gray dark:bg-gray/5">
                    <div class="flex w-full flex-col space-y-6">

                        @foreach ($componentGroups as $componentGroup)
                            <x-cachet::component-group :component-group="$componentGroup" />
                        @endforeach

                    </div>
                </div>
            </div>
        </section>

        {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_AFTER) }}
    @endif
</div>
