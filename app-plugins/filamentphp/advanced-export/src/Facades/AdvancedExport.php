<?php

namespace Filament\AdvancedExport\Facades;

use Filament\AdvancedExport\Support\ExportConfig;
use Illuminate\Support\Facades\Facade;

/**
 * @see ExportConfig
 */
class AdvancedExport extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ExportConfig::class;
    }
}
