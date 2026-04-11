<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Livewire\Components;

use Adultdate\Wirechat\Livewire\Widgets\Wirechat;
use Filament\Facades\Filament;

class ChatsSidebar extends Wirechat
{
    public function panelId(): ?string
    {
        return Filament::getCurrentPanel()?->getId();
    }

    public function render()
    {
        // Return the wire-chat-widget view directly - it will be wrapped by the modal in the render hook
        return view('wirechat::livewire.widgets.wire-chat-widget');
    }
}
