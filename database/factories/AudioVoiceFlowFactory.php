<?php

namespace Database\Factories;

use App\Models\AudioVoiceFlow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AudioVoiceFlow>
 */
class AudioVoiceFlowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'approved', 'active', 'archived'];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'filename' => 'audio/voice-'.$this->faker->uuid().'.mp3',
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->numberBetween(1, 100),
            'tags' => $this->faker->randomElements(['telemarketing', 'sales', 'outbound', 'inbound', 'follow-up', 'voicemail'], rand(1, 3)),
            'duration' => $this->faker->numberBetween(30, 300), // 30 seconds to 5 minutes
            'play_count' => $this->faker->numberBetween(0, 500),
        ];
    }
}
