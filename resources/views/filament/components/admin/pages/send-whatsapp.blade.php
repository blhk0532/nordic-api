<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-6">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
                    Send WhatsApp Message
                </h2>

                <form wire:submit="sendMessage" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex justify-end">
                        <x-filament::button
                            type="submit"
                            color="success"
                            icon="heroicon-o-paper-airplane"
                        >
                            Send Message
                        </x-filament::button>
                    </div>
                </form>
            </div>

            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                       
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            WhatsApp Integration
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <p>
                                This form allows you to send WhatsApp messages using your configured Evolution API instances.
                                Make sure your WhatsApp instance is connected and active before sending messages.
                            </p>
                            <p class="mt-2">
                                <strong>Note:</strong> Phone numbers should include the country code without the + symbol (e.g., 5511999999999 for Brazil).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
