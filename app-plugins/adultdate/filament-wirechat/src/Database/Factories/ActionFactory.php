<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Database\Factories;

use AdultDate\FilamentWirechat\Enums\Actions;
use AdultDate\FilamentWirechat\Models\Action;
use AdultDate\FilamentWirechat\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;

/**
 * @extends Factory<Action>
 */
class ActionFactory extends Factory
{
    protected $model = Action::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_type' => User::class,
            'actor_id' => User::factory(),
            'actionable_type' => Message::class,
            'actionable_id' => Message::factory(),
            'type' => Actions::DELETE->value,
            'data' => null,
        ];
    }

    /**
     * Indicate that the action is delete.
     */
    public function delete(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Actions::DELETE->value,
        ]);
    }

    /**
     * Indicate that the action is archive.
     */
    public function archive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Actions::ARCHIVE->value,
        ]);
    }

    /**
     * Indicate that the action is removed by admin.
     */
    public function removedByAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Actions::REMOVED_BY_ADMIN->value,
        ]);
    }

    /**
     * Add custom data to the action.
     */
    public function withData(array $data): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => json_encode($data),
        ]);
    }
}
