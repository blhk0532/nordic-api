{{-- Modal wrapper that contains the widget component --}}
<div 
    id="chats-sidebar"
    x-data="{ open: false }"
    x-show="open"
    x-on:open-modal.window="if ($event.detail.id === 'chats-sidebar') { open = true }"
    x-on:close-modal.window="if ($event.detail.id === 'chats-sidebar') { open = false }"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-hidden"
>
    <!-- Modal backdrop -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 transition-opacity"
        x-on:click="open = false"
    ></div>

    <!-- Modal panel - contains the widget Livewire component directly -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-[500px] sm:w-[500px] bg-white dark:bg-gray-800 shadow-xl overflow-hidden"
    >
        {{-- Render wire-chat-widget view with component context --}}
        <div class="h-full chats-sidebar-modal-widget" style="min-height: 100%; overflow: hidden;">
            @php
                // Render the wire-chat-widget view with this component's context
                echo view('wirechat::livewire.widgets.wire-chat-widget', [
                    'panelId' => $this->panelId(),
                    'selectedConversationId' => $this->selectedConversationId,
                    'widgetComponents' => $this->widgetComponents,
                    'activeWirechatWidgetComponent' => $this->activeWirechatWidgetComponent,
                ])->render();
            @endphp
        </div>
    </div>
</div>
