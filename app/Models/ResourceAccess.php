<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $resource
 * @property array<array-key, mixed> $role_access
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereResource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereRoleAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceAccess whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ResourceAccess extends Model
{
    protected $table = 'resource_accesses';

    protected $fillable = [
        'resource',
        'role_access',
        'is_active',
    ];

    protected $casts = [
        'role_access' => 'array',
        'is_active' => 'boolean',
    ];
}
