@php
    $uniqueId = 'graper-' . uniqid();
    $initialContent = $getStatePath() ? ($getRecord()?->content ?? null) : null;
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore
        x-data="{}"
        @graper.sync.window="$wire.set('data.content', $event.detail, false)"
    >
        <input type="hidden" id="{{ $uniqueId }}-input" value="{{ $initialContent ?? '' }}" />
        <div id="{{ $uniqueId }}" style="min-height: {{ $getMinHeight() }};"></div>
    </div>
</x-dynamic-component>