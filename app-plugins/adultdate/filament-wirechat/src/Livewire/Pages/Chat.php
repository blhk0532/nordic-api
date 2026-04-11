<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Pages;

use AdultDate\FilamentWirechat\Models\Conversation;
use Adultdate\Wirechat\Livewire\Concerns\HasPanel;
use Livewire\Attributes\Title;
use Livewire\Component;

class Chat extends Component
{
    use HasPanel;

    public $conversation;

    public function mount()
    {
        // /make sure user is authenticated
        abort_unless(auth()->check(), 401);

        // We remove deleted conversation incase the user decides to visit the delted conversation
        $this->conversation = Conversation::where('id', $this->conversation)->firstOrFail();

        // Check if the user belongs to the conversation
        abort_unless(auth()->user()->belongsToConversation($this->conversation), 403);

    }

    #[Title('Chats')]
    public function render()
    {
        return view('wirechat::livewire.pages.chat')
            ->layout($this->panel()->getLayout());
    }
}
