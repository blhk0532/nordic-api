@php
    // rendering this view should be a no-op when the AI chat API route is
    // missing, otherwise Blade will throw a RouteNotFoundException when the
    // view attempts to resolve the URL later in the script.  We also hide the
    // trigger button so the UI doesn't show a broken feature.
    $hasAiRoute = \Illuminate\Support\Facades\Route::has('api.ai.chat');
    $aiChatUrl = $hasAiRoute ? route('api.ai.chat') : '';
@endphp

@if($hasAiRoute)
    <div x-data="aiChat()" x-init="init()">
@else
    {{-- AI chat feature disabled because route not defined --}}
    <div>
@endif
    <button
        class="fi-icon-btn fi-size-md fi-topbar-database-notifications-btn"
        tooltip="AI Assistant"
        color="gray"
        size="lg"
        x-on:click.prevent="openModal()"
    >
        <x-filament::icon
            icon="ri-chat-smile-ai-3-line"
            class="fi-icon fi-size-lg"
        />
    </button>

    <div
        id="global-ai-search"
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-hidden fi-modal-slide-over-left"
    >
        <div
            x-show="isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm"
            x-on:click="closeModal()"
        ></div>
        <div
            x-show="isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="fixed inset-y-0 right-0 z-50 w-full bg-white dark:bg-gray-900 shadow-2xl overflow-hidden flex flex-col"
            style="height: 100vh; max-width: 640px;"
        >
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-600 to-primary-500">
                <div class="flex items-center gap-3">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-6 h-6 text-white" />
                    <h2 class="text-lg font-semibold text-white">AI Assistant</h2>
                </div>
                <div class="flex items-center gap-1">
                    <button x-on:click="newConversation()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg">
                        <x-filament::icon icon="heroicon-o-plus-circle" class="w-5 h-5" />
                    </button>
                    <button x-on:click="clearChat()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg">
                        <x-filament::icon icon="heroicon-o-trash" class="w-5 h-5" />
                    </button>
                    <button x-on:click="closeModal()" class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg">
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50 dark:bg-gray-900" id="chat-messages">
                <template x-for="(msg, idx) in messages" :key="idx">
                    <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3" :class="msg.role === 'user' ? 'bg-primary-600 text-white rounded-br-md' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-bl-md shadow-sm ring-1 ring-gray-200 dark:ring-gray-700'">
                            <template x-if="msg.role === 'assistant'">
                                <div class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <p class="text-sm whitespace-pre-wrap leading-relaxed" x-text="msg.content"></p>
                                </div>
                            </template>
                            <template x-if="msg.role === 'user'">
                                <p class="text-sm whitespace-pre-wrap leading-relaxed" x-text="msg.content"></p>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="isLoading" class="flex justify-start">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl rounded-bl-md shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-gray-400" />
                            <span class="text-sm text-gray-500 dark:text-gray-400">Tänker...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="fi-input-wrp flex flex-1 rounded-lg shadow-sm ring-1 bg-gray-100 dark:bg-gray-900 ring-gray-300 dark:ring-gray-600 focus-within:ring-primary-500 overflow-hidden">
                        <input
                            type="text"
                            x-model="input"
                            x-on:keydown.enter="sendMessage()"
                            placeholder="Skriv ett meddelande..."
                            class="fi-input block w-full border-none bg-transparent py-2.5 px-4 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-0"
                        >
                    </div>
                    <button
                        x-on:click="sendMessage()"
                        :disabled="isLoading || !input.trim()"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-all disabled:opacity-70 h-[42px] min-w-[42px]"
                    >
                        <span x-show="isLoading">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span x-show="!isLoading">
                            <x-filament::icon icon="heroicon-m-paper-airplane" class="w-4 h-4" />
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function aiChat() {
    return {
        isOpen: false,
        isLoading: false,
        input: '',
        messages: [
            { role: 'assistant', content: 'Hej! Jag är din AI-assistent. Jag kan hjälpa dig med frågor om dina kunder, bokningar eller annat. Vad kan jag hjälpa dig med idag?' }
        ],

        init() {
            this.loadMessages();
        },

        loadMessages() {
            const saved = localStorage.getItem('ai_chat_messages');
            if (saved) {
                this.messages = JSON.parse(saved);
            }
        },

        saveMessages() {
            localStorage.setItem('ai_chat_messages', JSON.stringify(this.messages));
        },

        openModal() {
            this.isOpen = true;
            this.$nextTick(() => {
                document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
            });
        },

        closeModal() {
            this.isOpen = false;
        },

        newConversation() {
            this.messages = [
                { role: 'assistant', content: 'Hej! Jag är din AI-assistent. Jag kan hjälpa dig med frågor om dina kunder, bokningar eller annat. Vad kan jag hjälpa dig med idag?' }
            ];
            this.saveMessages();
        },

        clearChat() {
            this.messages = [
                { role: 'assistant', content: 'Hej! Jag är din AI-assistent. Jag kan hjälpa dig med frågor om dina kunder, bokningar eller annat. Vad kan jag hjälpa dig med idag?' }
            ];
            this.saveMessages();
        },

        async sendMessage() {
            if (!this.input.trim() || this.isLoading) return;

            const userMessage = this.input.trim();
            this.input = '';
            this.isLoading = true;

            this.messages.push({ role: 'user', content: userMessage });
            this.saveMessages();

            this.$nextTick(() => {
                document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
            });

            try {
                // use the URL resolved at the top of the view (empty string if
                // the route was not available; but in that case the sendMessage
                // function will never run because UI is hidden).
                const response = await fetch('{{ $aiChatUrl }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        message: userMessage,
                        history: this.messages.slice(-10)
                    })
                });

                const data = await response.json();
                this.messages.push({ role: 'assistant', content: data.response });
            } catch (e) {
                this.messages.push({ role: 'assistant', content: 'Sorry, jag kunde inte få ett svar. Försök igen senare.' });
            }

            this.isLoading = false;
            this.saveMessages();

            this.$nextTick(() => {
                document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
            });
        }
    }
}
</script>
