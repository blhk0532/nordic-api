<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Actions;

use Filament\Actions\Action;

if (! class_exists(AdminAction::class)) {
    class AdminAction extends Action
    {
        protected function setUp(): void
        {
            parent::setUp();
        }

        public function adminAction(): Action
        {
            return Action::make('admin')
                ->requiresConfirmation()
                ->action(function (array $arguments) {
                    dd('Admin action called', $arguments);
                });
        }
    }
}
