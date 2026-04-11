<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Pages\WirechatPanel;

use AdultDate\FilamentWirechat\Livewire\Chat\Chat as ChatComponent;
use AdultDate\FilamentWirechat\Models\Conversation;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class WirechatChatPage extends Page
{
    public Conversation $conversation;

    protected string $view = 'filament-wirechat::filament.pages.wirechat-panel.chat';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'chat/{conversation}';

    protected static bool $fullWidth = true;

    public function mount(Conversation $conversation): void
    {
        abort_unless(auth()->check(), 401);
        $this->conversation = $conversation;
        abort_unless(auth()->user()->belongsToConversation($this->conversation), 403);
    }

    /**
     * Handle when the conversation route parameter changes (e.g., via wire:navigate in SPA mode).
     * This method is called by Livewire when the route parameter changes.
     */
    public function updatedConversation(Conversation $conversation): void
    {
        // Re-mount with the new conversation
        $this->mount($conversation);
    }

    public function getTitle(): string
    {
        if ($this->conversation->isGroup() && $this->conversation->group) {
            return $this->conversation->group->name ?? 'Group Chat';
        }

        if ($this->conversation->isPrivate()) {
            $peer = $this->conversation->peerParticipant(auth()->user());
            if ($peer && $peer->participantable) {
                return $peer->participantable->wirechat_name ?? 'Private Chat';
            }
        }

        return 'Chat';
    }

    /**
     * Hide the page header
     */
    public function getHeader(): ?View
    {
        return null;
    }

    protected function getViewData(): array
    {
        return [
            'chatComponent' => ChatComponent::class,
            'conversation' => $this->conversation,
        ];
    }
}
