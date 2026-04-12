<?php

namespace Database\Factories;

use App\Models\DialerCampaign;
use App\Models\DialerLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DialerLead>
 */
class DialerLeadFactory extends Factory
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
            'dialer_campaign_id' => DialerCampaign::factory(),
            'phone_number' => fake()->numerify('46#########'),
            'name' => fake()->name(),
            'status' => 'pending',
            'priority' => fake()->numberBetween(0, 10),
            'attempts_count' => 0,
            'last_attempted_at' => null,
            'last_disposition' => null,
            'meta' => null,
        ];
    }
}
