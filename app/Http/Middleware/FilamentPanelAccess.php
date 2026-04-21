<?php

declare(strict_types=1);

// app/Http/Middleware/CheckAdminAccess.php

namespace App\Http\Middleware;

use App\Models\PanelAccess;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

final class FilamentPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestPath = $request->path();
        $path = parse_url($requestPath, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', $path)));

        // Allow Livewire upload endpoints through
        if (str_contains($requestPath, 'livewire') && str_contains($requestPath, 'upload-file')) {
            return $next($request);
        }

        if (($i = array_search('auth', $segments)) !== false) {
            $panelId = $segments[$i + 1] ?? null;
        } else {
            $panelId = $segments[0] ?? null;
        }

        logger()->info("requestPath:: {$requestPath}");
        //    logger()->info("User ID:: {$user->id} with Role:: {$user->role} accesesPanel:: '{$panelId}'");

        if ($this->isAuthPage($segments, $panelId)) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $userRole = (string) ($user->role ?? '');

        if ($this->checkPanelAccess($panelId)) {
            return $next($request);
        }

        Notification::make()
            ->title('ACCESS DENIED ( ˶°ㅁ°)!!')
            ->body('การเข้าถึงถูกปฏิเสธ')
            ->warning()
            ->send();

        $previousUrl = url()->previous();

        if (empty($previousUrl) || $previousUrl === $request->fullUrl()) {
            $previousUrl = '/';
        }

        return redirect()->to($previousUrl)->with('error', 'Unauthorized access to this resource.');

        // return $next($request);
    }

    private function getPanelLoginRoute(?string $panelId): string
    {
        if ($panelId && Route::has($routeName = 'filament.'.$panelId.'.auth.login')) {
            return route($routeName);
        }

        return '/login';
    }

    private function isAuthPage(array $segments, ?string $panelId): bool
    {
        if ($panelId === null) {
            return false;
        }

        $authSlugs = ['login', 'register', 'password-reset', 'email-verification', 'profile'];

        // Find the position of panelId in segments
        $panelIndex = array_search($panelId, $segments);
        if ($panelIndex === false) {
            return false;
        }

        $nextSegment = $segments[$panelIndex + 1] ?? null;

        return in_array($nextSegment, $authSlugs, true);
    }

    public function checkPanelAccess($panelId): bool
    {

        //    logger()->info("panelId:: {$panelId}");

        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($panelId === 'app') {
            return true;
        }

        $panelAccess = PanelAccess::where('panel_id', $panelId)
            ->whereJsonContains('role_access', $user->role)
            ->where('is_active', true)
            ->first();

        return $panelAccess !== null;
    }
}
