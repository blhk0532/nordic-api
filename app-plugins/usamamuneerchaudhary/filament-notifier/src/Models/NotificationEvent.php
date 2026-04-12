<?php

namespace Usamamuneerchaudhary\Notifier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class NotificationEvent extends Model
{
    protected $table = 'notifier_events';

    protected $fillable = [
        'group',
        'name',
        'key',
        'description',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Check if settings column exists before trying to save
            try {
                $columns = Schema::getColumnListing($model->getTable());
                if (! in_array('settings', $columns)) {
                    // Remove settings from attributes if no column
                    unset($model->attributes['settings']);
                    unset($model->original['settings']);
                } elseif (empty($model->settings) || $model->settings === []) {

                    $model->settings = null;
                }
            } catch (\Exception $e) {
                // If we cant check, just unset it to be safe
                unset($model->attributes['settings']);
            }
        });
    }

    public function templates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'event_key', 'key');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }
}
