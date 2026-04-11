<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Contracts\Resourceable;
use Adultdate\FilamentBooking\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasResources
{
    public function getResourcesJs(): array
    {
        $resources = $this->getResources();

        if ($resources instanceof Builder) {
            $resources = $resources->get();
        }

        if (is_array($resources)) {
            $resources = collect($resources);
        }

        return $resources
            ->map(static function (Resourceable|CalendarResource $resource): array {
                if ($resource instanceof Resourceable) {
                    $resource = $resource->toCalendarResource();
                }

                return $resource->toCalendarObject();
            })
            ->toArray();
    }

    protected function getResources(): Collection|array|Builder
    {
        return [];
    }
}
