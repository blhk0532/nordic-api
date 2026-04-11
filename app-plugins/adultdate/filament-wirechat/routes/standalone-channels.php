<?php

declare(strict_types=1);

use Adultdate\Wirechat\Helpers\MorphClassResolver;
use Adultdate\Wirechat\Models\Conversation;
use Adultdate\Wirechat\PanelRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// Use Adultdate\Wirechat classes if available, fallback to AdultDate\FilamentWirechat
$morphClassResolver = class_exists(MorphClassResolver::class)
    ? MorphClassResolver::class
    : AdultDate\FilamentWirechat\Helpers\MorphClassResolver::class;

$conversationClass = class_exists(Conversation::class)
    ? Conversation::class
    : AdultDate\FilamentWirechat\Models\Conversation::class;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

$panels = app(PanelRegistry::class)->all();

if (empty($panels)) {
    Log::warning('No panels registered in wirechatPanelRegistry for channels');

    return;
}

foreach ($panels as $panel) {
    $panelId = $panel->getId();
    $guards = $panel->getGuards();
    $middleware = $panel->getMiddleware();

    // Conversation channel
    Broadcast::channel("{$panelId}.conversation.{conversationId}", function ($user, $conversationId) use ($guards, $conversationClass) {
        // If $user is already authenticated by the application's broadcast auth, use it
        if (! $user) {
            // Fallback to checking each guard defined in the panel
            $authenticatedUser = null;
            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    $authenticatedUser = Auth::guard($guard)->user();

                    break;
                }
            }
            $user = $authenticatedUser ?? null;

            if (! $user) {
                return false;
            }
        }

        $conversation = $conversationClass::find($conversationId);

        return $conversation && $user->belongsToConversation($conversation);
    }, [
        'guards' => $guards,
        'middleware' => $middleware,
    ]);

    // Participant channel
    Broadcast::channel("{$panelId}.participant.{encodedType}.{id}", function ($user, $encodedType, $id) use ($guards, $morphClassResolver) {
        // If $user is already authenticated by the application's broadcast auth, use it
        if (! $user) {
            // Fallback to checking each guard defined in the panel
            $authenticatedUser = null;
            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    $authenticatedUser = Auth::guard($guard)->user();

                    break;
                }
            }
            $user = $authenticatedUser ?? null;

            if (! $user) {
                return false;
            }
        }

        $morphType = $morphClassResolver::decode($encodedType);

        return $user->id === $id && $user->getMorphClass() === $morphType;
    }, [
        'guards' => $guards,
        'middleware' => $middleware,
    ]);
}
