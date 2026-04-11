@props([
    'conversation' => null, //Should be conversation  ID (Int)
    'widget' => false
])


<x-filament-wirechat::actions.open-chat-drawer 
        component="filament-wirechat.chat.group.info"
        dusk="show_group_info"
        conversation="{{$conversation}}"
        :widget="$widget"
        >
{{$slot}}
</x-filament-wirechat::actions.open-chat-drawer>
