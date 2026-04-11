@props([
    'conversation' => null, //Should be conversation  ID (Int)
    'widget' => false
])

@php
    // Ensure conversation is an integer
    $conversationId = $conversation ? (int)$conversation : null;
@endphp

<x-filament-wirechat::actions.open-chat-drawer 
        component="filament-wirechat.chat.info"
        dusk="show_chat_info"
        :conversation="$conversationId"
        :widget="$widget"
        >
{{$slot}}
</x-filament-wirechat::actions.open-chat-drawer>
