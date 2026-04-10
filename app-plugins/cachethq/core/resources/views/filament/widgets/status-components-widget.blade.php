 @include('cachet::filament.widgets.partials.cachet-widget-assets')
    <x-filament-widgets::widget id="status-components-widget" >
<div class="text-zinc-700 dark:text-zinc-300 w-full">
    @if ($ungroupedComponents->isNotEmpty())
        {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_BEFORE) }}

        <section class="fi-section" style="padding: 0px;" id="status-components-widget-section">
            <div class="w-full px-4 py-6 sm:px-6 lg:px-8">
                <div class="grid w-full gap-6 lg:grid-cols-3 col-span-3">
                    @foreach ($ungroupedComponents as $cachetComponent)
                        <x-filament-widgets::widget class="overflow-hidden">
                            <x-cachet::component-ungrouped :component="$cachetComponent" />
                        </x-filament-widgets::widget>
                    @endforeach
                </div>
            </div>
        </section>

        {{ \Cachet\Facades\CachetView::renderHook(\Cachet\View\RenderHook::STATUS_PAGE_BODY_AFTER) }}
    @endif
</div>
</x-filament-widgets::widget>
