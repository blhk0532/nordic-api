<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerLeadStatus;
use App\Jobs\CheckDialerCampaignQueueJob;
use App\Models\DialerCallAttempt;
use App\Services\DialerCampaignService;
use Clue\React\Ami\Protocol\Event as AmiEvent;
use Illuminate\Events\Dispatcher;

class AmiDialerEventSubscriber
{
    public function __construct(
        private DialerCampaignService $campaignService,
    ) {}

    public function subscribe(Dispatcher $events): void
    {
        $events->listen('ami.events.OriginateResponse', [$this, 'handleOriginateResponse']);
        $events->listen('ami.events.Dial', [$this, 'handleDial']);
        $events->listen('ami.events.Hangup', [$this, 'handleHangup']);
    }

    public function handleOriginateResponse(mixed ...$payload): void
    {
        $fields = $this->extractFields($payload);
        $actionId = $fields['ActionID'] ?? null;

        if (! $actionId) {
            return;
        }

        $attempt = DialerCallAttempt::query()
            ->where('ami_action_id', $actionId)
            ->latest('id')
            ->first();

        if (! $attempt) {
            return;
        }

        $response = strtolower((string) ($fields['Response'] ?? ''));

        if ($response === 'success') {
            $attempt->update([
                'status' => DialerAttemptStatus::Sent,
                'ami_unique_id' => $fields['Uniqueid'] ?? $attempt->ami_unique_id,
                'channel' => $fields['Channel'] ?? $attempt->channel,
                'raw_event' => $fields,
                'sent_at' => $attempt->sent_at ?? now(),
            ]);

            return;
        }

        $attempt->update([
            'status' => DialerAttemptStatus::Failed,
            'disposition' => 'originate_failed',
            'hangup_cause' => $fields['Message'] ?? null,
            'raw_event' => $fields,
            'ended_at' => now(),
        ]);

        $attempt->lead()->update([
            'status' => DialerLeadStatus::Failed,
            'last_disposition' => 'originate_failed',
        ]);
    }

    public function handleDial(mixed ...$payload): void
    {
        $fields = $this->extractFields($payload);
        $attempt = $this->findAttempt($fields);

        if (! $attempt) {
            return;
        }

        $dialStatus = strtoupper((string) ($fields['DialStatus'] ?? ''));

        if ($dialStatus === 'ANSWER') {
            $attempt->update([
                'status' => DialerAttemptStatus::Answered,
                'answered_at' => $attempt->answered_at ?? now(),
                'ami_unique_id' => $fields['Uniqueid'] ?? $attempt->ami_unique_id,
                'ami_linked_id' => $fields['Linkedid'] ?? $attempt->ami_linked_id,
                'raw_event' => $fields,
            ]);

            $attempt->lead()->update([
                'status' => DialerLeadStatus::Answered,
                'last_disposition' => 'answered',
            ]);

            return;
        }

        $attempt->update([
            'status' => DialerAttemptStatus::Ringing,
            'ami_unique_id' => $fields['Uniqueid'] ?? $attempt->ami_unique_id,
            'ami_linked_id' => $fields['Linkedid'] ?? $attempt->ami_linked_id,
            'raw_event' => $fields,
        ]);
    }

    public function handleHangup(mixed ...$payload): void
    {
        $fields = $this->extractFields($payload);
        $attempt = $this->findAttempt($fields);

        if (! $attempt) {
            return;
        }

        $endedAt = now();
        $answeredAt = $attempt->answered_at;

        $attempt->update([
            'status' => DialerAttemptStatus::Hangup,
            'disposition' => $this->resolveDisposition($fields, $attempt->answered_at !== null),
            'hangup_cause' => $fields['Cause-txt'] ?? $fields['Cause'] ?? null,
            'ami_unique_id' => $fields['Uniqueid'] ?? $attempt->ami_unique_id,
            'ami_linked_id' => $fields['Linkedid'] ?? $attempt->ami_linked_id,
            'raw_event' => $fields,
            'ended_at' => $endedAt,
            'duration_seconds' => $answeredAt ? $endedAt->diffInSeconds($answeredAt) : null,
        ]);

        $campaign = $attempt->campaign;
        $disposition = (string) $attempt->fresh()->disposition;
        $lead = $attempt->lead;
        $shouldRetry = $this->shouldRetryLead($attempt, $disposition);

        $lead->update([
            'status' => $shouldRetry
                ? DialerLeadStatus::Pending
                : $this->resolveLeadStatus($fields, $attempt->answered_at !== null),
            'last_disposition' => $disposition,
        ]);

        $campaign = $attempt->campaign;

        if ($campaign->isRunning()) {
            if ($shouldRetry) {
                CheckDialerCampaignQueueJob::dispatch($campaign->id)
                    ->delay(now()->addSeconds($campaign->retry_delay_seconds));

                $this->campaignService->queueNextBatch($campaign, 1);

                return;
            }

            $this->campaignService->queueNextBatch($campaign, 1);
        }
    }

    /**
     * @param  array<int, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function extractFields(array $payload): array
    {
        foreach ($payload as $item) {
            if ($item instanceof AmiEvent) {
                return [
                    'Event' => $item->getName(),
                    ...$item->getFields(),
                ];
            }

            if (is_array($item)) {
                return $item;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function findAttempt(array $fields): ?DialerCallAttempt
    {
        if (isset($fields['ActionID'])) {
            $attempt = DialerCallAttempt::query()
                ->where('ami_action_id', (string) $fields['ActionID'])
                ->latest('id')
                ->first();

            if ($attempt) {
                return $attempt;
            }
        }

        foreach (['Uniqueid', 'Linkedid'] as $field) {
            if (! isset($fields[$field])) {
                continue;
            }

            $attempt = DialerCallAttempt::query()
                ->where('ami_unique_id', (string) $fields[$field])
                ->orWhere('ami_linked_id', (string) $fields[$field])
                ->latest('id')
                ->first();

            if ($attempt) {
                return $attempt;
            }
        }

        if (isset($fields['Channel'])) {
            return DialerCallAttempt::query()
                ->where('channel', (string) $fields['Channel'])
                ->latest('id')
                ->first();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function resolveDisposition(array $fields, bool $wasAnswered): string
    {
        if ($wasAnswered) {
            return 'completed';
        }

        $cause = strtolower((string) ($fields['Cause-txt'] ?? ''));

        if (str_contains($cause, 'busy')) {
            return 'busy';
        }

        if (str_contains($cause, 'no answer') || str_contains($cause, 'normal clearing')) {
            return 'no_answer';
        }

        return 'failed';
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function resolveLeadStatus(array $fields, bool $wasAnswered): DialerLeadStatus
    {
        if ($wasAnswered) {
            return DialerLeadStatus::Completed;
        }

        $cause = strtolower((string) ($fields['Cause-txt'] ?? ''));

        if (str_contains($cause, 'busy')) {
            return DialerLeadStatus::Busy;
        }

        if (str_contains($cause, 'no answer') || str_contains($cause, 'normal clearing')) {
            return DialerLeadStatus::NoAnswer;
        }

        return DialerLeadStatus::Failed;
    }

    protected function shouldRetryLead(DialerCallAttempt $attempt, string $disposition): bool
    {
        if (! in_array($disposition, ['busy', 'no_answer'], true)) {
            return false;
        }

        return $attempt->lead->attempts_count < $attempt->campaign->max_attempts;
    }
}
