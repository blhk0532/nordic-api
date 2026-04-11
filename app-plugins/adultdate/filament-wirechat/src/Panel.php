<?php

declare(strict_types=1);

namespace Adultdate\Wirechat;

use Adultdate\Wirechat\Panel\Concerns\HasActions;
use Adultdate\Wirechat\Panel\Concerns\HasAttachments;
use Adultdate\Wirechat\Panel\Concerns\HasAuth;
use Adultdate\Wirechat\Panel\Concerns\HasBroadcasting;
use Adultdate\Wirechat\Panel\Concerns\HasChatActions;
use Adultdate\Wirechat\Panel\Concerns\HasChatMiddleware;
use Adultdate\Wirechat\Panel\Concerns\HasChatsSearch;
use Adultdate\Wirechat\Panel\Concerns\HasColors;
use Adultdate\Wirechat\Panel\Concerns\HasDeleteMessageActions;
use Adultdate\Wirechat\Panel\Concerns\HasEmojiPicker;
use Adultdate\Wirechat\Panel\Concerns\HasFavicon;
use Adultdate\Wirechat\Panel\Concerns\HasGroupActions;
use Adultdate\Wirechat\Panel\Concerns\HasGroups;
use Adultdate\Wirechat\Panel\Concerns\HasHeading;
use Adultdate\Wirechat\Panel\Concerns\HasHeart;
use Adultdate\Wirechat\Panel\Concerns\HasId;
use Adultdate\Wirechat\Panel\Concerns\HasLayout;
use Adultdate\Wirechat\Panel\Concerns\HasMiddleware;
use Adultdate\Wirechat\Panel\Concerns\HasRoutes;
use Adultdate\Wirechat\Panel\Concerns\HasSearchableAttributes;
use Adultdate\Wirechat\Panel\Concerns\HasUsersSearch;
use Adultdate\Wirechat\Panel\Concerns\HasWebPushNotifications;
use Adultdate\Wirechat\Support\EvaluatesClosures;
use Closure;

class Panel
{
    use EvaluatesClosures;
    use HasActions;
    use HasAttachments;
    use HasAuth;
    use HasBroadcasting;
    use HasChatActions;
    use HasChatMiddleware;
    use HasChatsSearch;
    use HasColors;
    use HasDeleteMessageActions;
    use HasEmojiPicker;
    use HasFavicon;
    use HasGroupActions;
    use HasGroups;
    use HasHeading;
    use HasHeart;
    use HasId;
    use HasLayout;
    use HasMiddleware;
    use HasRoutes;
    use HasSearchableAttributes;
    use HasUsersSearch;
    use HasWebPushNotifications;

    protected bool|Closure $isDefault = false;

    public static function make(): static
    {
        return app(self::class);

    }

    public function default(bool|Closure $condition = true): static
    {
        $this->isDefault = $condition;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->evaluate($this->isDefault);
    }

    public function register(): void
    {
        // WirechatColor::register($this->getColors());
    }
}
