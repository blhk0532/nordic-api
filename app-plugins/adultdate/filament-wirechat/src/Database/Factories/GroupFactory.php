<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Enums\GroupType;
use AdultDate\FilamentWirechat\Models\Conversation;
use AdultDate\FilamentWirechat\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory()->group(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => GroupType::PRIVATE->value,
            'allow_members_to_send_messages' => true,
            'allow_members_to_add_others' => true,
            'allow_members_to_edit_group_info' => false,
            'admins_must_approve_new_members' => false,
        ];
    }

    /**
     * Indicate that the group is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::PUBLIC->value,
        ]);
    }

    /**
     * Indicate that the group is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::PRIVATE->value,
        ]);
    }

    /**
     * Indicate that members cannot send messages.
     */
    public function membersCannotSendMessages(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_members_to_send_messages' => false,
        ]);
    }

    /**
     * Indicate that members cannot add others.
     */
    public function membersCannotAddOthers(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_members_to_add_others' => false,
        ]);
    }

    /**
     * Indicate that members can edit group info.
     */
    public function membersCanEditGroupInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_members_to_edit_group_info' => true,
        ]);
    }

    /**
     * Indicate that admins must approve new members.
     */
    public function adminsMustApproveMembers(): static
    {
        return $this->state(fn (array $attributes) => [
            'admins_must_approve_new_members' => true,
        ]);
    }
}
