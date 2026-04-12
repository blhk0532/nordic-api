<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasTeams;
use App\Enums\AuthRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'current_team_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasTeams, Notifiable, TwoFactorAuthenticatable;

    use HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'role' => AuthRole::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function getFilamentName(): string
    {
        return "{$this->name_first} {$this->name_last}";
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ownedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_owners')
            ->withTimestamps();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(self::class, 'author_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assigned_to_id');
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        $currentTeam = $this->currentTeam;

        if ($currentTeam) {
            return $currentTeam;
        }

        return $this->getTenants($panel)->first();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->ownedTeams()->get()->merge($this->teams)->sortBy('name');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->belongsToTeam($tenant);
    }
}
