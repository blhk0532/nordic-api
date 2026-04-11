<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Models\Scopes;

use AdultDate\FilamentWirechat\Enums\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WithoutRemovedActionScope implements Scope
{
    /**
     * Applies a scope to exclude particicipants removed from group by admins
     * unless new messages have been added after the "deletion" timestamp.
     *
     * @param  Builder  $builder  The Eloquent query builder instance.
     * @param  Model  $model  The model instance on which the scope is applied.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereDoesntHave('actions', function (Builder $query) {
            $query->where('type', Actions::REMOVED_BY_ADMIN);  // Filter actions that are of type 'remove'
        });
    }
}
