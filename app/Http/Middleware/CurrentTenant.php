<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CurrentTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('web')->check()) {
            $user = auth('web')->user();
            /** @var Team|null $tenant */
            $tenant = Filament::getTenant();
            if ($tenant?->id) {
                if ($user->current_team_id !== $tenant->id) {
                    $user->update(['current_team_id' => $tenant->id]);
                }
            }
        }

        return $next($request);
    }
}
