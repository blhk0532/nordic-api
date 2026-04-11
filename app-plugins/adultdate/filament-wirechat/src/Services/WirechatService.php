<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Services;

use Adultdate\Wirechat\Panel;
use Adultdate\Wirechat\PanelRegistry;

class WirechatService
{
    /**
     * Get the color used to be used in as theme
     */
    public static function getColor(): string
    {
        return config('filament-wirechat.color', '#3b82f6');
    }

    /**
     * Get the table prefix from the configuration.
     *
     * @return string|null The table prefix or null if not set.
     */
    public static function tablePrefix(): ?string
    {
        return config('filament-wirechat.table_prefix');
    }

    /**
     * Format the table name with the table prefix.
     *
     * @param  string  $table  The table name to format.
     * @return string The formatted table name.
     */
    public static function formatTableName(string $table): string
    {
        return config('filament-wirechat.table_prefix').$table;
    }

    /**
     * Check if the new group modal button can be shown.
     *
     * @return bool True if the new group modal button can be shown, false otherwise.
     */
    public static function showNewGroupModalButton(): bool
    {
        return config('filament-wirechat.show_new_group_modal_button', false);
    }

    /**
     * Check if the new chat modal button can be shown.
     *
     * @return bool True if the new chat modal button can be shown, false otherwise.
     */
    public static function showNewChatModalButton(): bool
    {
        return config('filament-wirechat.show_new_chat_modal_button', false);
    }

    /**
     * Get the maximum number of members allowed per group.
     *
     * @return int The maximum number of members.
     */
    public static function maxGroupMembers(): int
    {
        return (int) config('filament-wirechat.max_group_members', 1000);
    }

    /**
     * Get the wirechat storage folder from the configuration.
     *
     * @return string The storage folder.
     *
     * @deprecated Use Wirechat::storage()->directory() instead.
     */
    public static function storageFolder(): string
    {
        return (new StorageService)->attachmentsDirectory();
    }

    /**
     * Get the wirechat disk visibility from the configuration.
     *
     * @return string The disk visibility.
     *
     * @deprecated Use Wirechat::storage()->visibility() instead.
     */
    public static function diskVisibility(): string
    {
        return (new StorageService)->visibility();
    }

    /**
     * Get the wirechat disk visibility from the configuration.
     *
     * @return string The disk visibility.
     *
     * @deprecated Use Wirechat::storage()->visibility() instead.
     */
    public static function storageDisk(): string
    {
        return (new StorageService)->disk();
    }

    /**
     * Get the wirechat messages queue from the configuration.
     *
     * @return string The messages queue.
     */
    public static function messagesQueue(): string
    {
        return (string) config('filament-wirechat.broadcasting.messages_queue', 'default');
    }

    /**
     * Get the wirechat notifications queue from the configuration.
     *
     * @return string The notifications queue.
     */
    public static function notificationsQueue(): string
    {
        return (string) config('filament-wirechat.broadcasting.notifications_queue', 'default');
    }

    /**
     * Check if notifications are enabled for Wirechat.
     *
     * @return bool True if notifications are enabled, false otherwise.
     */
    public static function notificationsEnabled(): bool
    {
        return (bool) config('filament-wirechat.notifications.enabled', false);
    }

    /**
     * Determine if the application prefers to use UUIDs instead of
     * auto-incrementing IDs for the conversations table.
     *
     * This method first checks the new configuration key:
     * `filament-wirechat.uses_uuid_for_conversations`.
     *
     * For backwards compatibility, it will fall back to the old key:
     * `filament-wirechat.uuids` if the new one is not set.
     */
    public static function usesUuidForConversations(): bool
    {
        return (bool) config(
            'filament-wirechat.uses_uuid_for_conversations',
            config('filament-wirechat.uuids', false) // legacy fallback
        );
    }

    /**
     * Legacy method: Check if the application prefers to use UUIDs
     * for the conversations table.
     *
     * @deprecated since 0.4.0 Use {@see usesUuidForConversations()} instead.
     */
    public static function usesUuid(): bool
    {
        return self::usesUuidForConversations();
    }

    public function storage(): StorageService
    {
        return new StorageService;
    }

    /**
     * Get the default panel instance.
     *
     * @return Panel|null
     */
    public function getDefaultPanel()
    {
        if (class_exists(PanelRegistry::class) && app()->bound(PanelRegistry::class)) {
            return app(PanelRegistry::class)->getDefault();
        }

        return null;
    }

    /**
     * Get a panel by its ID.
     *
     * @return Panel|null
     */
    public function getPanel(string $panelId)
    {
        if (class_exists(PanelRegistry::class) && app()->bound(PanelRegistry::class)) {
            return app(PanelRegistry::class)->get($panelId);
        }

        return null;
    }

    /**
     * Get the current panel instance.
     *
     * @return Panel|null
     */
    public function currentPanel()
    {
        if (class_exists(PanelRegistry::class) && app()->bound(PanelRegistry::class)) {
            return app(PanelRegistry::class)->getCurrent();
        }

        return null;
    }
}
