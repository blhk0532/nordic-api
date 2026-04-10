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

class StatusOverviewWidget extends Widget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'cachet::filament.widgets.status-overview-widget';

    public Collection $componentGroups;

    public Collection $ungroupedComponents;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    public Collection $schedules;

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
}
