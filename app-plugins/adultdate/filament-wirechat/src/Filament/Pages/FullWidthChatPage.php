<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Pages;

use AdultDate\FilamentWirechat\Livewire\Chat\Chat as ChatComponent;
use AdultDate\FilamentWirechat\Models\Conversation;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class FullWidthChatPage extends Page
{
    public ?Conversation $conversation = null;

    protected string $view = 'filament-wirechat::livewire.pages.full-width-chat';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'full-width-chat/{conversation}';

    protected static ?string $title = 'Wirechat';

    protected static bool $fullWidth = true;

    public function mount(Conversation $conversation): void
    {
        // Ensure user is authenticated
        abort_unless(Auth::check(), 401);

        // Use route model binding - Filament will automatically resolve the Conversation
        $this->conversation = $conversation;

        // Check if the user belongs to the conversation
        abort_unless($conversation->participants()->where('participantable_type', get_class(Auth::user()))->where('participantable_id', Auth::id())->exists(), 403);
    }

    public function getTitle(): string
    {
        if (! $this->conversation) {
            return 'Chat';
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
