<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Livewire\Components;

use Adultdate\Wirechat\Helpers\MorphClassResolver;
use Filament\Facades\Filament;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatsIconButton extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshUnreadCount();
    }

    /**
     * Get event listeners for real-time updates.
     */
    public function getListeners(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [
                'refresh-unread-count' => 'refreshUnreadCount',
            ];
        }

        $encodedType = MorphClassResolver::encode($user->getMorphClass());
        $userId = $user->getKey();

        // Listen for new messages on the participant channel to update unread count
        $listeners = [
            'refresh-unread-count' => 'refreshUnreadCount',
        ];

        // Listen to all conversations for this user to detect new messages
        // We'll refresh the count when any message is created for this user
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'admin';
        $channelName = "{$panelId}.participant.{$encodedType}.{$userId}";
        $listeners["echo-private:{$channelName},.Adultdate\\Wirechat\\Events\\NotifyParticipant"] = 'handleNewMessage';

        return $listeners;
    }

    /**
     * Handle new message notification and refresh unread count.
     */
    public function handleNewMessage(): void
    {
        $this->refreshUnreadCount();
    }

    /**
     * Refresh the unread count from the database.
     */
    #[On('refresh-unread-count')]
    public function refreshUnreadCount(): void
    {
        $user = auth()->user();
        $this->unreadCount = $user?->getUnreadCount() ?? 0;
    }

    public function render()
    {
        return view('filament-wirechat::livewire.components.chats-icon-button');
    }
}
