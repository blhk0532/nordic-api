<div class="space-y-6">
    <div class="grid gap-4">
        <div>
            <label class="fi-label">
                WhatsApp Instance
            </label>
            <select
                wire:model.live="instanceId"
                class="fi-input"
            >
                <option value="">Select an instance</option>
                @foreach($this->instanceOptions as $option)
                    <option value="{{ $option['id'] }}">
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="fi-label">
                To Number
            </label>
            <input
                wire:model="toNumber"
                type="text"
                placeholder="66988808418"
                class="fi-input"
            />
            @error('toNumber')
                <p class="fi-field-description text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="fi-label">
                Message
            </label>
            <textarea
                wire:model="message"
                placeholder="Enter your message here..."
                rows="4"
                class="fi-input"
            ></textarea>
            @error('message')
                <p class="fi-field-description text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button
            wire:click="sendMessage"
            wire:loading.attr="disabled"
            class="fi-button fi-button-primary"
        >
            <span wire:loading.remove>Send Message</span>
            <span wire:loading>Sending...</span>
        </button>
    </div>

    @if($success || $error || $result)
        <div class="p-4 rounded-lg {{ $success ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
            <h3 class="text-lg font-bold mb-2 {{ $success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                {{ $success ? 'Success' : 'Error' }}
            </h3>

            @if($result)
                <pre class="whitespace-pre-wrap text-sm font-mono">{{ $result }}</pre>
            @endif
        </div>
    @endif

    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <p class="text-sm text-yellow-800 dark:text-yellow-200">
            <strong>Note:</strong> This will send a real WhatsApp message to the specified number (+66988808418).
            <br />
            Make sure you have the correct number before sending.
        </p>
    </div>
</div>
