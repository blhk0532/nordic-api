<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Middleware;

use AdultDate\FilamentWirechat\Models\Conversation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BelongsToConversation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Get the conversation parameter from route (could be string ID or Conversation model)
        $conversationParam = $request->route('conversation');

        // If it's a string, resolve it to a Conversation model
        if (is_string($conversationParam) || is_numeric($conversationParam)) {
            $conversation = Conversation::find($conversationParam);

            if (! $conversation) {
                abort(404, 'Conversation not found');
            }

            // Replace the route parameter with the resolved model for downstream use
            $request->route()->setParameter('conversation', $conversation);
        } else {
            $conversation = $conversationParam;
        }

        if (! $conversation instanceof Conversation) {
            abort(404, 'Conversation not found');
        }

        if (! $user || ! $user->belongsToConversation($conversation)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
