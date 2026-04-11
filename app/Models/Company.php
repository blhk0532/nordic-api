<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Wallo\FilamentCompanies\Company as FilamentCompaniesCompany;
use Wallo\FilamentCompanies\Events\CompanyCreated;
use Wallo\FilamentCompanies\Events\CompanyDeleted;
use Wallo\FilamentCompanies\Events\CompanyUpdated;

class Company extends FilamentCompaniesCompany implements HasAvatar
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (self $company): void {
            if ($company->user_id) {
                $company->owners()->syncWithoutDetaching([$company->user_id]);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'personal_company',
        'phone',
        'email',
        'website',
        'address',
        'city',
        'country',
        'org_number',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => CompanyCreated::class,
        'updated' => CompanyUpdated::class,
        'deleted' => CompanyDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_company' => 'boolean',
        ];
    }

    public function getFilamentAvatarUrl(): string
    {
        return $this->owner->profile_photo_url;
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_owners')
            ->withTimestamps();
    }

    /**
     * @param  array<array-key, Model>  $models
     */
    public function newCollection(array $models = []): EloquentCollection
    {
        return new EloquentCollection($models);
    }
}
