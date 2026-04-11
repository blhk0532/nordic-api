<?php

declare(strict_types=1);

// Prevent multiple includes
if (defined('FILAMENT_WIRECHAT_CHANNELS_LOADED')) {
    return;
}
define('FILAMENT_WIRECHAT_CHANNELS_LOADED', true);

use AdultDate\FilamentWirechat\Models\Conversation;
use Adultdate\Wirechat\Helpers\MorphClassResolver;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

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

// Get all Filament panels
$panels = Filament::getPanels();

foreach ($panels as $panel) {
    $panelId = $panel->getId();
    $guard = $panel->getAuthGuard();

    // Conversation channel
    Broadcast::channel("{$panelId}.conversation.{conversationId}", function ($user, $conversationId) use ($guard) {
        // Try all possible guards to find authenticated user
        if (! $user) {
            // Try panel guard first, then web guard, then default guard
            foreach ([$guard, 'web', null] as $testGuard) {
                if ($testGuard === null) {
                    if (Auth::check()) {
                        $user = Auth::user();

                        break;
                    }
                } elseif (Auth::guard($testGuard)->check()) {
                    $user = Auth::guard($testGuard)->user();

                    break;
                }
            }
        }

        // If still no user, deny access
        if (! $user) {
            Log::warning("Broadcast auth failed for channel {$panelId}.conversation - no authenticated user", [
                'guard' => $guard,
                'conversationId' => $conversationId,
            ]);

            return false;
        }

        $conversation = Conversation::find($conversationId);

        if (! $conversation) {
            Log::warning("Broadcast auth failed for channel {$panelId}.conversation - conversation not found", [
                'conversationId' => $conversationId,
            ]);

            return false;
        }

        $belongsTo = $user->belongsToConversation($conversation);

        if (! $belongsTo) {
            Log::warning("Broadcast auth failed for channel {$panelId}.conversation - user does not belong to conversation", [
                'user_id' => $user->id,
                'conversationId' => $conversationId,
            ]);
        }

        return $belongsTo;
    }, [
        'guards' => array_filter([$guard, 'web']), // Allow both panel guard and web guard
    ]);

    // Participant channel
    Broadcast::channel("{$panelId}.participant.{encodedType}.{id}", function ($user, $encodedType, $id) use ($guard, $panelId) {
        // $user should be authenticated by middleware, but we check multiple guards as fallback
        if (! $user) {
            // Try all possible guards
            foreach ([$guard, 'web', null] as $testGuard) {
                if ($testGuard === null) {
                    if (Auth::check()) {
                        $user = Auth::user();

                        break;
                    }
                } elseif (Auth::guard($testGuard)->check()) {
                    $user = Auth::guard($testGuard)->user();

                    break;
                }
            }
        }

        if (! $user) {
            return false;
        }

        try {
            $morphType = MorphClassResolver::decode($encodedType);
            $isAuthorized = $user->id === $id && $user->getMorphClass() === $morphType;

            return $isAuthorized;
        } catch (Exception $e) {
            Log::error("Broadcast auth error for channel {$panelId}.participant", [
                'error' => $e->getMessage(),
                'guard' => $guard,
                'encodedType' => $encodedType,
                'id' => $id,
            ]);

            return false;
        }
    }, [
        'guards' => array_filter([$guard, 'web']), // Allow both panel guard and web guard
    ]);
}
