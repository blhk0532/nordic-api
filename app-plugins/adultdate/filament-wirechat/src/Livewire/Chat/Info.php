<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Chat;

use AdultDate\FilamentWirechat\Models\Conversation;
use Adultdate\Wirechat\Livewire\Chats\Chats;
use Adultdate\Wirechat\Livewire\Concerns\HasPanel;
use Adultdate\Wirechat\Livewire\Concerns\ModalComponent;
use Adultdate\Wirechat\Livewire\Concerns\Widget;
use Livewire\Attributes\Locked;

class Info extends ModalComponent
{
    use HasPanel;
    use Widget;

    #[Locked]
    public Conversation $conversation;

    public static function closeModalOnEscapeIsForceful(): bool
    {
        return false;
    }

    /**
     * -----------------------------
     * Delete Chat
     * */
    public function deleteChat()
    {
        abort_unless(auth()->check(), 401);

        abort_unless(auth()->user()->belongsToConversation($this->conversation), 403);
        abort_unless($this->conversation->isSelf() || $this->conversation->isPrivate(), 403, 'This operation is not available for Groups.');

        // delete conversation
        $this->conversation->deleteFor(auth()->user());

        // redirect to chats page pr
        // Dispatach event instead if isWidget
        // handle widget termination
        $this->handleComponentTermination(
            redirectRoute: $this->panel()->chatsRoute(),
            events: [
                'close-chat',
                Chats::class => ['chat-deleted',  [$this->conversation->id]],
            ]
        );

    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <x-wirechat::loading-spin class="m-auto" />
        </div>
        HTML;
    }

    public function mount()
    {

        abort_if(empty($this->conversation), 404);

        abort_unless(auth()->check(), 401);
        abort_unless(auth()->user()->belongsToConversation($this->conversation), 403);

        abort_if($this->conversation->isGroup(), 403, __('wirechat::chat.info.messages.invalid_conversation_type_error'));

        // load participants
        $this->conversation->load('participants.participantable');

    }

    public function render()
    {

        $receiver = $this->conversation->peerParticipant(auth()->user())?->participantable;

        // Pass data to the view
        return view('wirechat::livewire.chat.info', [
            'receiver' => $receiver,
            'wirechat_avatar_url' => $receiver?->wirechat_avatar_url,
        ]);
    }
}
