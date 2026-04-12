<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerCampaignStatus;
use App\Enums\DialerLeadStatus;
use App\Jobs\OriginateAmiCallJob;
use App\Models\DialerCallAttempt;
use App\Models\DialerCampaign;
use App\Models\DialerLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class DialerCampaignService
{
    public function startCampaign(DialerCampaign $campaign): int
    {
        $campaign->update([
            'status' => DialerCampaignStatus::Running,
            'started_at' => $campaign->started_at ?? now(),
            'stopped_at' => null,
        ]);

        return $this->queueNextBatch($campaign);
    }

    public function pauseCampaign(DialerCampaign $campaign): void
    {
        $campaign->update([
            'status' => DialerCampaignStatus::Paused,
        ]);
    }

    public function stopCampaign(DialerCampaign $campaign): void
    {
        $campaign->update([
            'status' => DialerCampaignStatus::Stopped,
            'stopped_at' => now(),
        ]);
    }

    public function queueNextBatch(DialerCampaign $campaign, ?int $limit = null): int
    {
        if (! $campaign->isRunning()) {
            return 0;
        }

        $leads = $this->eligibleLeadsQuery($campaign)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->limit($limit ?? $campaign->max_concurrent_calls)
            ->get();

        if ($leads->isEmpty()) {
            if ($this->hasActiveAttempts($campaign) || $this->hasCoolingDownLeads($campaign)) {
                return 0;
            }

            $campaign->update([
                'status' => DialerCampaignStatus::Completed,
                'stopped_at' => now(),
            ]);

            return 0;
        }

        return $this->queueLeads($campaign, $leads)->count();
    }

    /**
     * @param  Collection<int, DialerLead>  $leads
     * @return Collection<int, DialerCallAttempt>
     */
    protected function queueLeads(DialerCampaign $campaign, Collection $leads): Collection
    {
        return $leads->map(function ($lead) use ($campaign): DialerCallAttempt {
            $actionId = (string) str()->uuid();

            $lead->update([
                'status' => DialerLeadStatus::Dialing,
            ]);

            $attempt = DialerCallAttempt::query()->create([
                'team_id' => $campaign->team_id,
                'dialer_campaign_id' => $campaign->id,
                'dialer_lead_id' => $lead->id,
                'status' => DialerAttemptStatus::Queued,
                'ami_action_id' => $actionId,
                'channel' => $campaign->source_channel,
                'destination' => $lead->phone_number,
            ]);

            OriginateAmiCallJob::dispatch(
                channel: $campaign->source_channel,
                extension: $lead->phone_number,
                context: $campaign->context,
                priority: 1,
                variables: [
                    'campaign_id' => (string) $campaign->id,
                    'lead_id' => (string) $lead->id,
                    'attempt_id' => (string) $attempt->id,
                ],
                callerId: $campaign->caller_id,
                timeoutMilliseconds: 30000,
                attemptId: $attempt->id,
                actionId: $actionId,
            );

            return $attempt;
        });
    }

    protected function eligibleLeadsQuery(DialerCampaign $campaign): HasMany
    {
        return $campaign->leads()
            ->where('status', DialerLeadStatus::Pending->value)
            ->where('attempts_count', '<', $campaign->max_attempts)
            ->where(function (Builder $query) use ($campaign): void {
                $query->whereNull('last_attempted_at')
                    ->orWhere('last_attempted_at', '<=', now()->subSeconds($campaign->retry_delay_seconds));
            });
    }

    protected function hasActiveAttempts(DialerCampaign $campaign): bool
    {
        return $campaign->attempts()
            ->whereIn('status', [
                DialerAttemptStatus::Queued->value,
                DialerAttemptStatus::Sent->value,
                DialerAttemptStatus::Ringing->value,
                DialerAttemptStatus::Answered->value,
            ])
            ->exists();
    }

    protected function hasCoolingDownLeads(DialerCampaign $campaign): bool
    {
        return $campaign->leads()
            ->where('status', DialerLeadStatus::Pending->value)
            ->where('attempts_count', '<', $campaign->max_attempts)
            ->whereNotNull('last_attempted_at')
            ->where('last_attempted_at', '>', now()->subSeconds($campaign->retry_delay_seconds))
            ->exists();
    }
}
