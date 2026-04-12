<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DialerCampaign;
use App\Services\DialerCampaignService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckDialerCampaignQueueJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $campaignId,
        public ?int $limit = 1,
    ) {
        $this->onQueue('dialer');
    }

    public function handle(DialerCampaignService $campaignService): void
    {
        $campaign = DialerCampaign::query()->find($this->campaignId);

        if (! $campaign || ! $campaign->isRunning()) {
            return;
        }

        $campaignService->queueNextBatch($campaign, $this->limit);
    }
}
