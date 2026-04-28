@php
    $uniqueId = 'graper-' . uniqid();
    $statePath = $getStatePath();
    $initialContent = $getState() ?? $getRecord()?->content ?? null;
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore>
        <div id="{{ $uniqueId }}" style="min-height: {{ $getMinHeight() }};"></div>
    </div>
    <input
        type="hidden"
        id="{{ $uniqueId }}-input"
        value="{{ $initialContent ?? '' }}"
        data-state-path="{{ $statePath }}"
    />
</x-dynamic-component>