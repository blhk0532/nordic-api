<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Traits;

use AdultDate\FilamentWirechat\Models\Action;

/**
 * Trait Actionable
 */
trait Actor
{
    /**
     * ----------------------------------------
     * ----------------------------------------
     * Actions - that were performed by this model
     * --------------------------------------------
     */
    public function performedActions()
    {
        return $this->morphMany(Action::class, 'actor', 'actor_type', 'actor_id', 'id');
    }
}
