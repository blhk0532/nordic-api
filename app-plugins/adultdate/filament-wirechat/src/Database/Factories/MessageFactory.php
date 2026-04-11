<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Enums\MessageType;
use AdultDate\FilamentWirechat\Models\Conversation;
use AdultDate\FilamentWirechat\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sendable_type' => User::class,
            'sendable_id' => User::factory(),
            'body' => fake()->sentence(),
            'type' => MessageType::TEXT->value,
            'reply_id' => null,
            'kept_at' => null,
        ];
    }

    /**
     * Indicate that the message is an attachment.
     */
    public function attachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MessageType::ATTACHMENT->value,
            'body' => null,
        ]);
    }

    /**
     * Indicate that the message is a reply.
     */
    public function reply(Message $parentMessage): static
    {
        return $this->state(fn (array $attributes) => [
            'reply_id' => $parentMessage->id,
        ]);
    }

    /**
     * Indicate that the message is kept from disappearing.
     */
    public function kept(): static
    {
        return $this->state(fn (array $attributes) => [
            'kept_at' => now(),
        ]);
    }
}
