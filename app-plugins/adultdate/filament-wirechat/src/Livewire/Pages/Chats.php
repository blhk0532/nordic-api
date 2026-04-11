<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Pages;

use Adultdate\Wirechat\Livewire\Concerns\HasPanel;
use Livewire\Attributes\Title;
use Livewire\Component;

class Chats extends Component
{
    use HasPanel;

    #[Title('Chats')]
    public function render()
    {

        return view('wirechat::livewire.pages.chats')
            ->layout($this->panel()->getLayout());

    }
}
