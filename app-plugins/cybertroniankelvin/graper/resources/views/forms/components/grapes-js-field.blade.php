<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:ignore
        x-data="{
            init() {
                const editor = grapesjs.init({
                    container: '#{{ $getId() }}',
                    height: '{{ $getHeight() }}',
                    storageManager: false,
                });

                const state = @js($getState());
                if (state && typeof state === 'object' && Object.keys(state).length > 0) {
                    editor.loadProjectData(state);
                }

                editor.on('update', () => {
                    $wire.set('{{ $getStatePath() }}', editor.getProjectData());
                });
            }
        }"
        x-init="init()"
    >
        <div
            id="{{ $getId() }}"
            style="height: {{ $getHeight() }}; border: 1px solid #e5e7eb; border-radius: 0.375rem; overflow: hidden;"
        ></div>
    </div>
</x-dynamic-component>
