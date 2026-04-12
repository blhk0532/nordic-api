<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\DialerCampaignStatus;
use App\Jobs\OriginateAmiCallJob;
use App\Models\DialerCampaign;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DialpadWidget extends Widget
{
    protected string $view = 'filament.widgets.dialpad-widget';

    protected int|string|array $columnSpan = 'full';

    public ?int $campaignId = null;

    public string $number = '';

    public function mount(): void
    {
        $campaign = $this->campaignQuery()
            ->where('status', DialerCampaignStatus::Running->value)
            ->latest('id')
            ->first();

        $this->campaignId = $campaign?->id;
    }

    public function appendDigit(string $digit): void
    {
        if (! preg_match('/^[0-9+#*]$/', $digit)) {
            return;
        }

        if (strlen($this->number) >= 20) {
            return;
        }

        $this->number .= $digit;
    }

    public function backspace(): void
    {
        $this->number = substr($this->number, 0, -1);
    }

    public function clear(): void
    {
        $this->number = '';
    }

    public function placeCall(): void
    {
        $campaign = $this->currentCampaign();
        $number = trim($this->number);

        if (! $campaign) {
            Notification::make()->title('Select a campaign first')->danger()->send();

            return;
        }

        if ($number === '') {
            Notification::make()->title('Enter a number to dial')->warning()->send();

            return;
        }

        OriginateAmiCallJob::dispatch(
            channel: $campaign->source_channel,
            extension: $number,
            context: $campaign->context,
            priority: 1,
            variables: [
                'campaign_id' => (string) $campaign->id,
                'manual' => '1',
            ],
            callerId: $campaign->caller_id,
            timeoutMilliseconds: 30000,
        );

        Notification::make()->title('Call queued')->success()->send();
    }

    public function getCampaignsProperty(): Collection
    {
        return $this->campaignQuery()
            ->whereIn('status', [
                DialerCampaignStatus::Running->value,
                DialerCampaignStatus::Paused->value,
            ])
            ->orderByDesc('id')
            ->get();
    }

    protected function currentCampaign(): ?DialerCampaign
    {
        if (! $this->campaignId) {
            return null;
        }

        return $this->campaignQuery()->find($this->campaignId);
    }

    protected function campaignQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return DialerCampaign::query()
            ->when($tenant !== null, fn (Builder $query) => $query->where('team_id', $tenant->id));
    }
}
