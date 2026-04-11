<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => '',
            'password' => '',
            'remember' => true,
        ]);
    }
}
