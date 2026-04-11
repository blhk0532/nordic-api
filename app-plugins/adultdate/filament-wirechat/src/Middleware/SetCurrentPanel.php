<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Middleware;

use Adultdate\Wirechat\PanelRegistry;
use Closure;
use Illuminate\Http\Request;

class SetCurrentPanel
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $panelId)
    {
        app(PanelRegistry::class)->setCurrent($panelId);

        return $next($request);
    }
}
