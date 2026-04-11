@props([
    'component', 
    'conversation' => null,
    'widget' => false
])

@php
    // Ensure conversation is an integer or null
    $conversationId = $conversation ? (int)$conversation : null;
    $widgetBool = $widget ? 'true' : 'false';
@endphp

<div {{ $attributes->merge(['class' => 'cursor-pointer']) }}  
    onclick="
        console.log('openChatDrawer clicked', {
            component: '{{ $component }}',
            conversation: {{ $conversationId ?? 'null' }},
            widget: {{ $widgetBool }}
        });
        event.preventDefault();
        event.stopPropagation();
        // Dispatch to Livewire Drawer component globally
        // The drawer component should be listening via getListeners()
        Livewire.dispatch('openChatDrawer', {
            component: '{{ $component }}',
            arguments: {
                conversation: {{ $conversationId ?? 'null' }},
                widget: {{ $widgetBool }}
            }
        });
    "
    style="cursor: pointer;">

    {{ $slot }}
</div>
