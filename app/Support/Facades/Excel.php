<?php

declare(strict_types=1);

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array toArray(mixed $import, string $filePath, ?string $sheet = null)
 * @method static void import(mixed $import, string $filePath, ?string $disk = null)
 * @method static void export(mixed $export, string $filePath)
 *
 * @see \App\Support\Excel\Excel
 */
class Excel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Support\Excel\Excel::class;
    }
}
