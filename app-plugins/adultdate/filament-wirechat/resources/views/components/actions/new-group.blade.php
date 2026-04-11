@props([
    'widget' => false,
    'panel'=>null

])


<x-filament-wirechat::actions.open-modal
        component="filament-wirechat.new.group"
        :widget="$widget"
        :panel="$panel"

        >
{{$slot}}
</x-filament-wirechat::actions.open-modal>
