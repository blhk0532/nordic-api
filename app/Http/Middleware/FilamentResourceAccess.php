<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ResourceAccess;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class FilamentResourceAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAuthPage($request)) {
            return $next($request);
        }

        // Allow Livewire upload endpoints through
        if ($request->path() && str_contains($request->path(), 'livewire') && str_contains($request->path(), 'upload-file')) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $userRole = (string) ($user->role ?? '');

        if ($this->checkResourceAccess($request, $userRole)) {
            return $next($request);
        }

        //    Notification::make()
        //       ->title('Unauthorized 🛇o(≧o≦)o🛇 การเข้าถึงถูกปฏิเสธ')
        //        ->body('Oops, you do not have sufficient permissions
        //        Can\'t manage the resource ⊹ ACCESS DENIED
        //        System is currently in development and some
        //        Features are restricted due to maintenances.
        //        If you believe this is a mistake, contact admin')
        //        ->warning()
        //        ->send();

        Notification::make()
            ->title('Access Deinied o(≧o≦)🛇')
            ->body('การเข้าถึงถูกปฏิเสธ')
            ->danger()
            ->send();

        $previousUrl = url()->previous();

        if (empty($previousUrl) || $previousUrl === $request->fullUrl()) {
            $previousUrl = '/';
        }

        return redirect()->to($previousUrl)->with('error', 'Unauthorized access to this resource.');
    }

    private function isAuthPage(Request $request): bool
    {
        $authSlugs = ['login', 'register', 'password-reset', 'email-verification', 'profile'];
        $lastSegment = $request->segment(count($request->segments()));

        return in_array($lastSegment, $authSlugs, true);
    }

    public function checkResourceAccess(Request $request, string $userRole): bool
    {
        $resourceRules = ResourceAccess::query()
            ->where('is_active', true)
            ->get(['resource', 'role_access']);

        if ($resourceRules->isEmpty()) {
            return true;
        }

        $path = trim($request->path(), '/');
        $url = trim((string) $request->url(), '/');
        $fullUrl = trim((string) $request->fullUrl(), '/');
        $lastSegment = Str::afterLast($path, '/');
        $candidates = array_values(array_filter(array_unique([$path, $url, $fullUrl, $lastSegment])));

        $matchedRules = $resourceRules->filter(
            fn (ResourceAccess $rule): bool => $this->matchesAnyResourcePattern((string) $rule->resource, $candidates)
        );

        if ($matchedRules->isEmpty()) {
            return true;
        }

        return $matchedRules->contains(function (ResourceAccess $rule) use ($userRole): bool {
            $allowedRoles = is_array($rule->role_access) ? $rule->role_access : [];

            return in_array($userRole, $allowedRoles, true);
        });
    }

    /**
     * @param  array<int, string>  $candidates
     */
    private function matchesAnyResourcePattern(string $resourcePattern, array $candidates): bool
    {
        foreach ($candidates as $candidate) {
            if ($this->matchesResourcePattern($resourcePattern, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function matchesResourcePattern(string $resourcePattern, string $candidate): bool
    {
        if ($resourcePattern === '') {
            return false;
        }

        if ($resourcePattern === $candidate) {
            return true;
        }

        if (Str::contains($resourcePattern, '*') && Str::is($resourcePattern, $candidate)) {
            return true;
        }

        $compiledPattern = $this->compileRegexPattern($resourcePattern);
        if ($compiledPattern === null) {
            return false;
        }

        return @preg_match($compiledPattern, $candidate) === 1;
    }

    private function compileRegexPattern(string $resourcePattern): ?string
    {
        if (Str::startsWith($resourcePattern, 'regex:')) {
            $regexPattern = Str::after($resourcePattern, 'regex:');

            if ($regexPattern === '') {
                return null;
            }

            if ($this->isDelimitedRegex($regexPattern)) {
                return $regexPattern;
            }

            return '#'.$regexPattern.'#';
        }

        if ($this->isDelimitedRegex($resourcePattern)) {
            return $resourcePattern;
        }

        return null;
    }

    private function isDelimitedRegex(string $pattern): bool
    {
        return (bool) preg_match('/^([#~\/]).+\1[imsxuADSUXJ]*$/', $pattern);
    }
}
