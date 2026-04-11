@props([
    'component',
    'conversation' => null,
    'widget' => false,
    'panel'=>null,
])

<div  onclick="Livewire.dispatch('openWirechatModal', '{{ $component }}', {
        conversation:`{{$conversation ?? null }}`,
        widget:@js($widget),
        panel:@js($panel)
    })">

    {{ $slot }}
</div>
