<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Cachet\Models\Component;
use Cachet\Models\ComponentGroup;
use Cachet\Models\Incident;
use Cachet\Models\IncidentTemplate;
use Cachet\Models\Metric;
use Cachet\Models\Schedule;
use Cachet\Models\Subscriber;
use Cachet\Models\WebhookSubscription;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response;

final class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(HttpRequest): (Response)  $next
     */
    public function handle(HttpRequest $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $scopedModels = [
                Component::class,
                ComponentGroup::class,
                Incident::class,
                IncidentTemplate::class,
                Metric::class,
                Schedule::class,
                Subscriber::class,
                WebhookSubscription::class,
            ];

            foreach ($scopedModels as $model) {
                $model::addGlobalScope(
                    'tenant',
                    fn (Builder $query) => $query->where((new $model)->getTable().'.team_id', $tenant->id),
                );
            }
        }

        return $next($request);
    }
}
