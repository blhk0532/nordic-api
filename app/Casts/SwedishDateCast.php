<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SwedishDateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @return Carbon|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (empty($value) || $value === '\\N' || $value === '\N') {
            return null;
        }

        try {
            // Try to parse Swedish date format (YYYY-MM-DD)
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  mixed  $value
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '\\N' || $value === '\N') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        return $value;
    }
}
