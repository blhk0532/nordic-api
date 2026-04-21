<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $panel_id
 * @property array<array-key, mixed> $role_access
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess wherePanelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess whereRoleAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelAccess whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PanelAccess extends Model
{
    protected $fillable = [
        'panel_id',
        'role_access',
        'is_active',
    ];

    protected $casts = [
        'role_access' => 'array',
        'is_active' => 'boolean',
    ];
}
