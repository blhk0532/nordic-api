<?php

namespace Database\Factories;

use App\Models\DialerCallAttempt;
use App\Models\DialerCampaign;
use App\Models\DialerLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DialerCallAttempt>
 */
class DialerCallAttemptFactory extends Factory
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
            'dialer_lead_id' => DialerLead::factory(),
            'status' => 'queued',
            'ami_action_id' => (string) fake()->uuid(),
            'ami_unique_id' => null,
            'ami_linked_id' => null,
            'channel' => 'SIP/1001',
            'destination' => fake()->numerify('46#########'),
            'disposition' => null,
            'hangup_cause' => null,
            'raw_event' => null,
            'sent_at' => null,
            'answered_at' => null,
            'ended_at' => null,
            'duration_seconds' => null,
        ];
    }
}
