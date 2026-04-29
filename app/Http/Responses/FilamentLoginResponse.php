<?php

namespace App\Http\Responses;

use App\Models\Team;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class FilamentLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            $user = $request->user();
            $tenant = $user?->currentTeam ?? $user?->getTenants(Filament::getCurrentPanel())->first();
        }

        if ($tenant instanceof Team) {
            Filament::setTenant($tenant);
        }

        return redirect()->to(Filament::getUrl($tenant));
    }
}
