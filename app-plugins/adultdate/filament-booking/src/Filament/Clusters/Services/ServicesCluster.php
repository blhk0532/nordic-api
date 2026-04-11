<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services;

use BackedEnum;
use Filament\Clusters\Cluster;
use UnitEnum;

class ServicesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|UnitEnum|null $navigationGroup = 'Bokningar Admin';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Tjänster';

    protected static ?string $slug = 'booking/services';
}
