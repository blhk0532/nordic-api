<?php

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerCampaignStatus;
use App\Enums\DialerLeadStatus;
use App\Jobs\CheckDialerCampaignQueueJob;
use App\Jobs\OriginateAmiCallJob;
use App\Listeners\AmiDialerEventSubscriber;
use App\Models\DialerCallAttempt;
use App\Models\DialerCampaign;
use App\Models\DialerLead;
use App\Services\DialerCampaignService;
use Illuminate\Support\Facades\Queue;

test('starting campaign queues pending leads', function () {
    Queue::fake();

    $campaign = DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Draft,
        'source_channel' => 'SIP/1001',
        'context' => 'default',
        'max_concurrent_calls' => 2,
    ]);

    DialerLead::factory()->for($campaign, 'campaign')->create(['phone_number' => '46700000001']);
    DialerLead::factory()->for($campaign, 'campaign')->create(['phone_number' => '46700000002']);
    DialerLead::factory()->for($campaign, 'campaign')->create(['phone_number' => '46700000003']);

    $queued = app(DialerCampaignService::class)->startCampaign($campaign);

    expect($queued)->toBe(2)
        ->and($campaign->fresh()->status)->toBe(DialerCampaignStatus::Running)
        ->and(DialerCallAttempt::query()->count())->toBe(2);

    Queue::assertPushed(OriginateAmiCallJob::class, 2);
});

test('originate response marks attempt as sent', function () {
    $campaign = DialerCampaign::factory()->create();
    $lead = DialerLead::factory()->for($campaign, 'campaign')->create();

    $attempt = DialerCallAttempt::factory()->create([
        'dialer_campaign_id' => $campaign->id,
        'dialer_lead_id' => $lead->id,
        'ami_action_id' => 'action-123',
        'status' => DialerAttemptStatus::Queued,
    ]);

    app(AmiDialerEventSubscriber::class)->handleOriginateResponse([
        'ActionID' => 'action-123',
        'Response' => 'Success',
        'Uniqueid' => '171111.1',
        'Channel' => 'SIP/1001-0000001',
    ]);

    $attempt->refresh();

    expect($attempt->status)->toBe(DialerAttemptStatus::Sent)
        ->and($attempt->ami_unique_id)->toBe('171111.1');
});

test('hangup event completes answered lead', function () {
    $campaign = DialerCampaign::factory()->create();
    $lead = DialerLead::factory()->for($campaign, 'campaign')->create([
        'status' => DialerLeadStatus::Answered,
    ]);

    $attempt = DialerCallAttempt::factory()->create([
        'dialer_campaign_id' => $campaign->id,
        'dialer_lead_id' => $lead->id,
        'status' => DialerAttemptStatus::Answered,
        'ami_unique_id' => '171111.2',
        'answered_at' => now()->subSeconds(5),
    ]);

    app(AmiDialerEventSubscriber::class)->handleHangup([
        'Uniqueid' => '171111.2',
        'Cause-txt' => 'Normal Clearing',
    ]);

    $attempt->refresh();
    $lead->refresh();

    expect($attempt->status)->toBe(DialerAttemptStatus::Hangup)
        ->and($attempt->disposition)->toBe('completed')
        ->and($lead->status)->toBe(DialerLeadStatus::Completed);
});

test('busy hangup returns retryable lead to pending and schedules a wake-up job', function () {
    Queue::fake();

    $campaign = DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Running,
        'max_attempts' => 3,
        'retry_delay_seconds' => 45,
    ]);

    $lead = DialerLead::factory()->for($campaign, 'campaign')->create([
        'status' => DialerLeadStatus::Dialing,
        'attempts_count' => 1,
        'last_attempted_at' => now(),
    ]);

    $attempt = DialerCallAttempt::factory()->create([
        'dialer_campaign_id' => $campaign->id,
        'dialer_lead_id' => $lead->id,
        'status' => DialerAttemptStatus::Sent,
        'ami_unique_id' => '171111.3',
    ]);

    app(AmiDialerEventSubscriber::class)->handleHangup([
        'Uniqueid' => '171111.3',
        'Cause-txt' => 'User Busy',
    ]);

    $lead->refresh();
    $attempt->refresh();

    expect($attempt->disposition)->toBe('busy')
        ->and($lead->status)->toBe(DialerLeadStatus::Pending)
        ->and($lead->last_disposition)->toBe('busy');

    Queue::assertPushed(CheckDialerCampaignQueueJob::class);
});

test('cooling-down leads do not complete a running campaign and are queued after cooldown', function () {
    Queue::fake();

    $campaign = DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Running,
        'max_concurrent_calls' => 1,
        'max_attempts' => 3,
        'retry_delay_seconds' => 30,
    ]);

    $lead = DialerLead::factory()->for($campaign, 'campaign')->create([
        'status' => DialerLeadStatus::Pending,
        'attempts_count' => 1,
        'last_attempted_at' => now(),
    ]);

    $queuedDuringCooldown = app(DialerCampaignService::class)->queueNextBatch($campaign);

    expect($queuedDuringCooldown)->toBe(0)
        ->and($campaign->fresh()->status)->toBe(DialerCampaignStatus::Running);

    $lead->update([
        'last_attempted_at' => now()->subSeconds(31),
    ]);

    (new CheckDialerCampaignQueueJob($campaign->id))->handle(app(DialerCampaignService::class));

    expect(DialerCallAttempt::query()->count())->toBe(1)
        ->and($lead->fresh()->status)->toBe(DialerLeadStatus::Dialing);

    Queue::assertPushed(OriginateAmiCallJob::class, 1);
});
