<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Services;

class StorageService
{
    /**
     * Get the wirechat storage disk from the configuration.
     *
     * @return string The storage disk.
     */
    public static function disk(): string
    {
        return (string) config('filament-wirechat.storage.disk')
            ?: config('filament-wirechat.attachments.storage_disk', 'public');
    }

    /**
     * Get the configured storage visibility for Wirechat.
     *
     * Reads from `filament-wirechat.storage.visibility`.
     * Falls back to `filament-wirechat.attachments.disk_visibility`.
     *
     * @return string Either 'public' or 'private'.
     */
    public static function visibility(): string
    {
        return (string) config('filament-wirechat.storage.visibility')
            ?: config('filament-wirechat.attachments.disk_visibility', 'public');
    }

    /**
     * --------------------
     * Directories
     * i.e attachments , reports etc
     * -----------------
     */

    /**
     * Attachments directory
     *
     * @return string The storage directory path.
     */
    public static function attachmentsDirectory(): string
    {
        return (string) config('filament-wirechat.storage.directories.attachments')
            ?: config('filament-wirechat.attachments.storage_folder', 'attachments');
    }
}
