<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Pages;

use AdultDate\FilamentWirechat\Filament\Widgets\ChatsWidget;
use App\Models\User as Model;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Assets\Css;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ChatsDashboard extends Page
{
    protected static ?string $slug = 'my-chats';

    protected string $view = 'filament-wirechat::filament.pages.chats-dashboard';

    protected static ?string $title = '';

    protected static string|UnitEnum|null $navigationGroup = 'Mina Sidor';

    protected static ?string $navigationLabel = 'Meddelande';

    protected static ?int $navigationSort = 10;

    protected static ?int $sort = 10;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationLabel(): string
    {
        return $panel ?? 'Meddelande';
    }

    public function getView(): string
    {
        return 'filament-wirechat::filament.pages.chats-dashboard';
    }

    /**
     * Get the navigation badge for unread messages count.
     * Returns null when count is 0 so badge doesn't display.
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $notifiableType = $user instanceof \Illuminate\Database\Eloquent\Model ? $user->getMorphClass() : get_class($user);
        $userId = $user instanceof \Illuminate\Database\Eloquent\Model ? $user->getKey() : ($user->id ?? null);
        if (is_null($userId)) {
            return null;
        }
        $unreadCount = DatabaseNotification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();

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
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $notifiableType = $user instanceof \Illuminate\Database\Eloquent\Model ? $user->getMorphClass() : get_class($user);
        $userId = $user instanceof \Illuminate\Database\Eloquent\Model ? $user->getKey() : ($user->id ?? null);
        if (is_null($userId)) {
            return null;
        }

        $unreadCount = DatabaseNotification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();

        // Only return color if there are unread messages
        return $unreadCount > 0 ? 'success' : 'gray';
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl();
    }

    public function getMaxContentWidth(): Width
    {

        return Width::Full;
    }

    protected function getHeaderWidgets(): array
    {
        FilamentAsset::register([
            Css::make('chat', __DIR__.'/../../resources/css/chat.css'),
        ]);

        return [
            //    ChatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
