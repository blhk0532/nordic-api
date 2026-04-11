<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Pages;

use AdultDate\FilamentWirechat\Livewire\Chats\Chats as ChatsComponent;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class ChatsPage extends Page
{
    protected static ?string $slug = 'chats';

    protected static UnitEnum|string|null $navigationGroup = 'Chats';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament-wirechat::livewire.pages.chats';

    protected static ?string $navigationLabel = 'Wirechat';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = '  ';

    protected static bool $shouldRegisterNavigation = false;

    protected static bool $fullWidth = true;

    /**
     * Get the navigation badge for unread messages count.
     * Returns null when count is 0 so badge doesn't display.
     */
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $unreadCount = $user->getUnreadCount() ?? 0;

        // Return null if count is 0 so badge doesn't display
        if ($unreadCount === 0) {
            return null;
        }

        // Return formatted count (cap at 99+)
        return $unreadCount > 99 ? '99+' : (string) $unreadCount;
    }

    /**
     * Get the navigation badge color.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $unreadCount = $user->getUnreadCount() ?? 0;

        // Only return color if there are unread messages
        return $unreadCount > 0 ? 'danger' : null;
    }

    public function getHeading(): string
    {
        return ' ';
    }

    public function mount(): void
    {
        // Ensure user is authenticated
        abort_unless(auth()->check(), 401);
    }

    protected function getViewData(): array
    {
        return [
            'chatsComponent' => ChatsComponent::class,
        ];
    }
}
