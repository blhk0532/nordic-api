<?php

declare(strict_types=1);

namespace App\Services;

use MmesDesign\FilamentFileManager\DTOs\DirectoryListing;
use MmesDesign\FilamentFileManager\Enums\SortDirection;
use MmesDesign\FilamentFileManager\Enums\SortField;
use MmesDesign\FilamentFileManager\Services\FileManagerService;

/**
 * Overrides the default FileManagerService to skip persistent caching.
 *
 * The parent uses Cache::remember() with a 60-second TTL, serialising
 * DirectoryListing PHP objects into the persistent cache store. Under
 * Laravel Octane this causes "__PHP_Incomplete_Class" TypeErrors when
 * the cached blob is retrieved and PHP cannot reconstruct the class.
 *
 * Bypassing the cache is safe: the file manager is interactive and
 * listings are small; removing one 60-second cache layer has no
 * meaningful performance impact.
 */
class OctaneSafeFileManagerService extends FileManagerService
{
    protected function getCachedListing(
        string $disk,
        string $path,
        SortField $sortField,
        SortDirection $sortDirection,
    ): DirectoryListing {
        return $this->buildDirectoryListing($disk, $path, $sortField, $sortDirection);
    }
}
