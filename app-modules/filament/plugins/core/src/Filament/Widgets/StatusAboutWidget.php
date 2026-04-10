<?php

declare(strict_types=1);

namespace Cachet\Filament\Widgets;

use Cachet\Models\Component;
use Cachet\Models\ComponentGroup;
use Cachet\Models\Schedule;
use Cachet\Settings\AppSettings;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StatusAboutWidget extends Widget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1000;

    /**
     * @var view-string
     */
    protected string $view = 'cachet::filament.widgets.status-about';

    public Collection $componentGroups;

    public Collection $ungroupedComponents;

    public Collection $schedules;

    protected static bool $isDiscovered = true;

    public bool $display_graphs = false;

    public function mount(AppSettings $appSettings): void
    {
        $isAuthenticated = Auth::check();

        $this->componentGroups = ComponentGroup::query()
            ->with(['components' => fn ($query) => $query->enabled()->orderBy('order')->withCount('incidents')])
            ->visible($isAuthenticated)
            ->orderBy('order')
            ->when($isAuthenticated, fn (Builder $query) => $query->users(), fn ($query) => $query->guests())
            ->get();

        $this->ungroupedComponents = Component::query()
            ->enabled()
            ->whereNull('component_group_id')
            ->orderBy('order')
            ->withCount('incidents')
            ->get();

        $this->schedules = Schedule::query()
            ->with(['updates', 'components'])
            ->incomplete()
            ->orderBy('scheduled_at')
            ->get();

        $this->display_graphs = (bool) $appSettings->display_graphs;
    }

    public function getConsiderSupportingBlock()
    {
        return preg_replace(
            '/\*(.*?)\*/',
            '<x-filament::link href="https://github.com/" target="_blank" rel="nofollow noopener">$1</x-filament::link>',
            __('cachet::cachet.support.consider_supporting')
        );
    }

    public function getKeepUpToDateBlock()
    {
        return preg_replace(
            '/\*(.*?)\*/',
            '<x-filament::link href="https://ndsth.com/blog" target="_blank" rel="nofollow noopener">$1</x-filament::link>',
            __('cachet::cachet.support.keep_up_to_date')
        );
    }
}
