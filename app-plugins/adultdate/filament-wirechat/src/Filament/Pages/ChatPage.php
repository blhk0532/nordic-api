<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Pages;

use AdultDate\FilamentWirechat\Models\Conversation;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ChatPage extends Page
{
    public ?Conversation $conversation = null;

    protected string $view = 'filament-wirechat::livewire.pages.chat';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = ' ';

    protected static ?string $slug = 'chats/{conversation}';

    public function mount(Conversation $conversation): void
    {
        // Ensure user is authenticated
        abort_unless(Auth::check(), 401);

        // Use route model binding - Filament will automatically resolve the Conversation
        $this->conversation = $conversation;

        // Check if the user belongs to the conversation
        abort_unless(
            $this->conversation->participants()->where('participantable_id', Auth::id())->where('participantable_type', get_class(Auth::user()))->exists(),
            403
        );
    }

    /**
     * Handle when the conversation route parameter changes via wire:navigate.
     */
    public function updatedConversation(Conversation $conversation): void
    {
        $this->mount($conversation);
    }

    public function getTitle(): string
    {
        if (! $this->conversation) {
            return ' ';
        }

        if ($this->conversation->isGroup() && $this->conversation->group) {
            return $this->conversation->group->name ?? 'Group Chat';
        }

        if ($this->conversation->isPrivate()) {
            $peer = $this->conversation->peerParticipant(Auth::user());
            if ($peer && $peer->participantable) {
                return $peer->participantable->wirechat_name ?? 'Private Chat';
            }
        }

        return ' ';
    }

    public function getHeading(): string
    {
        return ' ';
    }

    protected function getViewData(): array
    {
        return [
            'conversation' => $this->conversation,
        ];
    }
}
