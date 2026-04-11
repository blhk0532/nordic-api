<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Enums\ConversationType;
use AdultDate\FilamentWirechat\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ConversationType::PRIVATE->value,
            'disappearing_started_at' => null,
            'disappearing_duration' => null,
        ];
    }

    /**
     * Indicate that the conversation is a group.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ConversationType::GROUP->value,
        ]);
    }

    /**
     * Indicate that the conversation is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ConversationType::PRIVATE->value,
        ]);
    }

    /**
     * Indicate that the conversation is self.
     */
    public function self(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ConversationType::SELF->value,
        ]);
    }

    /**
     * Indicate that the conversation has disappearing messages.
     */
    public function withDisappearing(int $minutes = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'disappearing_started_at' => now(),
            'disappearing_duration' => $minutes,
        ]);
    }
}
