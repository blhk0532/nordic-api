@props([
    'component',
    'conversation' => null,
    'widget' => false,
    'panel'=>null,
])

<div  onclick="console.log('Open modal clicked:', '{{ $component }}'); Livewire.dispatch('openWirechatModal', {
        component: '{{ $component }}',
        arguments: {
            conversation:`{{$conversation ?? null }}`,
            widget:@js($widget),
            panel:@js($panel)
        }
    }); console.log('Event dispatched');">

    {{ $slot }}
</div>
