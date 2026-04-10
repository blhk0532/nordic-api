<?php

declare(strict_types=1);

namespace Cachet\Concerns;

use Filament\Facades\Filament;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (self $model): void {
            if (! $model->team_id) {
                $tenant = Filament::getTenant();

                if ($tenant) {
                    $model->team_id = $tenant->id;
                }
            }
        });
    }
}
