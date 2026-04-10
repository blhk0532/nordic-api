<?php

namespace Cachet\Concerns;

use Cachet\Enums\ResourceVisibilityEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @method static Builder<static>|static query()
 * @method static Builder<static>|static visible(bool $authenticated = false)
 * @method static Builder<static>|static visibility(ResourceVisibilityEnum $visibility)
 * @method static Builder<static>|static users()
 * @method static Builder<static>|static guests()
 */
trait HasVisibility
{
    /**
     * Scope to visible incidents.
     */
    public function scopeVisible(Builder $query, bool $authenticated = false): void
    {
        $query->where(function (Builder $q) use ($authenticated): void {
            $q->whereIn('visible', match ($authenticated) {
                true => ResourceVisibilityEnum::visibleToUsers(),
                default => ResourceVisibilityEnum::visibleToGuests(),
            });

            if ($authenticated && ($user = Auth::user())) {
                $teamIds = $user->teams->pluck('id')->toArray();

                if (! empty($teamIds)) {
                    $q->orWhere(function (Builder $inner) use ($teamIds): void {
                        $inner->where('visible', ResourceVisibilityEnum::team)
                            ->whereIn('team_id', $teamIds);
                    });
                }
            }
        });
    }

    /**
     * Scope the resource to a given visibility setting.
     */
    public function scopeVisibility(Builder $query, ResourceVisibilityEnum $visibility): void
    {
        $query->where('visible', $visibility);
    }

    /**
     * Scope the resource to those visible to guests.
     */
    public function scopeGuests(Builder $query): void
    {
        $query->whereIn('visible', ResourceVisibilityEnum::visibleToGuests());
    }

    /**
     * Scope the resource to those visible to authenticated users.
     */
    public function scopeUsers(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->whereIn('visible', ResourceVisibilityEnum::visibleToUsers());

            if ($user = Auth::user()) {
                $teamIds = $user->teams->pluck('id')->toArray();

                if (! empty($teamIds)) {
                    $q->orWhere(function (Builder $inner) use ($teamIds): void {
                        $inner->where('visible', ResourceVisibilityEnum::team)
                            ->whereIn('team_id', $teamIds);
                    });
                }
            }
        });
    }
}
