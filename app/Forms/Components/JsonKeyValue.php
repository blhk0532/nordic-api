<?php

namespace App\Forms\Components;

use Filament\Forms\Components\KeyValue;

class JsonKeyValue extends KeyValue
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (JsonKeyValue $component, $state) {
            if (! is_array($state) || empty($state)) {
                $component->state([]);

                return;
            }

            // Get first value - if it's an array, use it; otherwise use the full state
            $first = current($state);
            $component->state(is_array($first) ? $first : $state);
        });

        $this->dehydrateStateUsing(function (JsonKeyValue $component, $state) {
            if (empty($state)) {
                return null;
            }

            // Store as [{"key": "value"}] format like DB expects
            return json_encode([$state]);
        });
    }
}
