<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;

class AddressForm extends Field
{
    /** @var string|callable|null */
    public $relationship = null;

    protected string $view = 'filament-schemas::components.grid';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (AddressForm $component, ?Model $record): void {
            $address = $record?->getRelationValue($this->getRelationship());

            $component->state($address ? $address->toArray() : [
                'country' => null,
                'street' => null,
                'city' => null,
                'state' => null,
                'zip' => null,
            ]);
        });

        $this->dehydrated(false);
    }

    public function relationship(string|callable $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function saveRelationships(): void
    {
        $state = $this->getState();
        $record = $this->getRecord();
        $relationship = $record?->{$this->getRelationship()}();

        if ($relationship === null) {
            return;
        }
        if ($address = $relationship->first()) {
            $address->update($state);
        } else {
            $relationship->updateOrCreate($state);
        }

        if ($record instanceof Model) {
            $record->touch();
        }
    }

    public function getDefaultChildComponents(): array
    {
        return [
            TextInput::make('street')
                ->label('Street address')
                ->maxLength(255),
            Grid::make(3)
                ->schema([
                    TextInput::make('zip')
                        ->label('Postal code')
                        ->maxLength(20),
                    TextInput::make('city')
                        ->maxLength(255),
                    TextInput::make('country')
                        ->placeholder('Sweden'),
                ]),
        ];
    }

    public function getRelationship(): string
    {
        return $this->evaluate($this->relationship) ?? $this->getName();
    }
}
