<?php

declare(strict_types=1);

namespace Awcodes\Overlook\Widgets;

use Closure;
use Awcodes\Overlook\Contracts\CustomizeOverlookWidget;
use Awcodes\Overlook\OverlookPlugin;
use Exception;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;


class OverlookWidget extends Widget
{
    public array $data = [];

    public array $excludes = [];

    public array $includes = [];

    public array $grid = [];

    public array $icons = [];

    protected string $view = 'overlook::widget';

    protected int|string|array $columnSpan = 'full';

    public static function getSort(): int
    {
        return OverlookPlugin::get()->getSort();
    }

    /**
     * @throws Exception
     */
    public function mount(): void
    {
        $this->data = $this->getData();

        if ($this->grid === []) {
            $this->grid = OverlookPlugin::get()->getColumns();
        }
    }

    public function convertCount(string|int|float $number): string
    {
        if (OverlookPlugin::get()->shouldAbbreviateCount()) {
            return Number::abbreviate((int) $number);
        }

        return $this->formatRawCount($number);
    }

    public function formatRawCount(string|int|float $number): string
    {
        return number_format((int) $number);
    }

    /**
     * @throws Exception
     */
    public function getData(): array
    {
        $plugin = OverlookPlugin::get();
        $includes = filled($this->includes) ? $this->includes : $plugin->getIncludes();
        $excludes = filled($this->excludes) ? $this->excludes : $plugin->getExcludes();
        $icons = filled($this->icons) ? $this->icons : $plugin->getIcons();

        $rawResources = filled($includes)
            ? $includes
            : filament()->getCurrentOrDefaultPanel()->getResources();

        return collect($rawResources)->filter(fn ($resource): bool => ! in_array($resource, $excludes))->transform(function ($resource) use ($plugin, $icons): ?array {

            $customIcon = array_search($resource, $icons);

            $res = app($resource);

            $widgetQuery = $res->getEloquentQuery();

            if ($plugin->shouldExcludeTrashed() && in_array(SoftDeletes::class, class_uses_recursive($widgetQuery->getModel()))) {
                $widgetQuery = $widgetQuery->withoutTrashed();
            }

            if ($res instanceof CustomizeOverlookWidget) {
                $rawCount = $res->getOverlookWidgetQuery($widgetQuery)->count();
                $title = $res->getOverlookWidgetTitle();
            } else {
                $rawCount = $widgetQuery->count();
                $title = ucfirst((string) $res->getPluralModelLabel());
            }

            if ($res->canViewAny()) {
                try {
                    $url = $res->getUrl('index');
                } catch (\Exception) {
                    $url = null;
                }

                return [
                    'name' => $title,
                    'sort' => $res->getNavigationSort() ?? 0,
                    'raw_count' => $this->formatRawcount($rawCount),
                    'count' => $this->convertCount($rawCount),
                    'sort_count' => (int) $rawCount,
                    'icon' => $customIcon ?: $res->getNavigationIcon(),
                    'url' => $url,
                ];
            }

            return null;
        })
            ->filter()
            ->filter(fn (array $item): bool => $item['sort_count'] >= 1)
            ->sortBy([['sort', 'asc'], ['sort', 'desc']])
            ->values()
            ->toArray();
    }

    public function shouldShowTooltips(string $number): bool
    {
        $plugin = OverlookPlugin::get();

        return mb_strlen($number) >= 4 && $plugin->shouldAbbreviateCount() && $plugin->shouldShowTooltips();
    }

        public function getIncludes(): array
    {
        return $this->evaluate($this->includes) ?? [];
    }


    public function includes(array|Closure $resources): static
    {
        $this->includes = $resources;

        return $this;
    }
}
