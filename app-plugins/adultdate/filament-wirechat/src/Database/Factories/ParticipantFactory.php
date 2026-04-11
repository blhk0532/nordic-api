<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Enums\ParticipantRole;
use AdultDate\FilamentWirechat\Models\Conversation;
use AdultDate\FilamentWirechat\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'participantable_type' => User::class,
            'participantable_id' => User::factory(),
            'role' => ParticipantRole::PARTICIPANT->value,
            'exited_at' => null,
            'conversation_deleted_at' => null,
            'conversation_cleared_at' => null,
            'conversation_read_at' => now(),
            'last_active_at' => now(),
        ];
    }

    /**
     * Indicate that the participant is an owner.
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => ParticipantRole::OWNER->value,
        ]);
    }

    /**
     * Indicate that the participant is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => ParticipantRole::ADMIN->value,
        ]);
    }

    /**
     * Indicate that the participant has exited.
     */
    public function exited(): static
    {
        return $this->state(fn (array $attributes) => [
            'exited_at' => now(),
        ]);
    }

    /**
     * Indicate that the participant has deleted the conversation.
     */
    public function deletedConversation(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_deleted_at' => now(),
        ]);
    }

    /**
     * Indicate that the participant has cleared the conversation.
     */
    public function clearedConversation(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_cleared_at' => now(),
        ]);
    }

    /**
     * Indicate that the participant has unread messages.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_read_at' => null,
        ]);
    }
}
