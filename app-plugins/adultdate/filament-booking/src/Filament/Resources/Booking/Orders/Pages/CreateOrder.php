<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\OrderResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Schemas\OrderForm;
use Adultdate\FilamentBooking\Models\Booking\Order;
use Adultdate\FilamentBooking\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    /**
     * @return array<Step>
     */
    protected function getSteps(): array
    {
        return [
            Step::make('Order Details')
                ->schema([
                    Section::make()
                        ->schema(OrderForm::getDetailsComponents())
                        ->columns(),
                ]),

            Step::make('Order Items')
                ->schema([
                    Section::make()
                        ->schema([OrderForm::getItemsRepeater()]),
                ]),
        ];
    }

    protected function afterCreate(): void
    {
        /** @var Order $order */
        $order = $this->record;

        /** @var User $user */
        $user = Auth::user();

        Notification::make()
            ->title('New order')
            ->icon('heroicon-o-shopping-bag')
            ->body("**{$order->customer?->name} ordered {$order->items->count()} products.**")
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase([$user]);
    }
}
