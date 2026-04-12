<?php

namespace Database\Factories;

use App\Models\DialerCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DialerCampaign>
 */
class DialerCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => null,
            'name' => fake()->sentence(3),
            'status' => 'draft',
            'source_channel' => 'SIP/1001',
            'context' => 'default',
            'caller_id' => fake()->numerify('1###'),
            'max_concurrent_calls' => 1,
            'max_attempts' => 1,
            'retry_delay_seconds' => 30,
            'started_at' => null,
            'stopped_at' => null,
        ];
    }
}
