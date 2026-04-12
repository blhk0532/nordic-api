<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerCampaignStatus;
use App\Enums\DialerLeadStatus;
use App\Models\DialerCallAttempt;
use App\Models\DialerCampaign;
use App\Models\DialerLead;
use App\Services\DialerCampaignService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class DialerControl extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.dialer-control';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoneArrowUpRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::PhoneArrowUpRight;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?string $navigationLabel = 'Campaign Dialer';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Campaign Dialer';

    public ?array $campaignData = [];

    public ?array $leadImportData = [];

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return null;
    }

    public function mount(): void
    {
        $this->campaignForm->fill([
            'name' => null,
            'source_channel' => 'SIP/1001',
            'context' => 'default',
            'caller_id' => null,
            'max_concurrent_calls' => 1,
            'max_attempts' => 1,
            'retry_delay_seconds' => 30,
        ]);

        $this->leadImportForm->fill([
            'dialer_campaign_id' => null,
            'numbers' => null,
        ]);
    }

    public function campaignForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('campaignData')
            ->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('source_channel')->required()->maxLength(255),
                TextInput::make('context')->required()->maxLength(255),
                TextInput::make('caller_id')->maxLength(255),
                TextInput::make('max_concurrent_calls')->numeric()->minValue(1)->required(),
                TextInput::make('max_attempts')->numeric()->minValue(1)->required(),
                TextInput::make('retry_delay_seconds')->numeric()->minValue(1)->required(),
            ]);
    }

    public function leadImportForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('leadImportData')
            ->schema([
                Select::make('dialer_campaign_id')
                    ->label('Campaign')
                    ->options($this->getCampaignOptions())
                    ->searchable()
                    ->required(),
                Textarea::make('numbers')
                    ->label('Leads (one phone per line, optional format: name,phone)')
                    ->rows(8)
                    ->required(),
            ]);
    }

    public function createCampaign(): void
    {
        $data = $this->campaignForm->getState();
        $tenant = Filament::getTenant();

        $campaign = DialerCampaign::query()->create([
            ...$data,
            'team_id' => $tenant?->id,
            'status' => DialerCampaignStatus::Draft,
        ]);

        $this->campaignForm->fill([
            'name' => null,
            'source_channel' => $campaign->source_channel,
            'context' => $campaign->context,
            'caller_id' => null,
            'max_concurrent_calls' => $campaign->max_concurrent_calls,
            'max_attempts' => $campaign->max_attempts,
            'retry_delay_seconds' => $campaign->retry_delay_seconds,
        ]);

        Notification::make()->title('Campaign created')->success()->send();
    }

    public function importLeads(): void
    {
        $data = $this->leadImportForm->getState();
        $campaign = $this->campaignQuery()->findOrFail((int) $data['dialer_campaign_id']);

        $rows = collect(preg_split('/\r\n|\r|\n/', (string) $data['numbers']) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter();

        $created = 0;

        foreach ($rows as $row) {
            [$name, $number] = str_contains($row, ',')
                ? array_map('trim', explode(',', $row, 2))
                : [null, trim($row)];

            if ($number === '') {
                continue;
            }

            $lead = DialerLead::query()->firstOrCreate(
                [
                    'dialer_campaign_id' => $campaign->id,
                    'phone_number' => $number,
                ],
                [
                    'team_id' => $campaign->team_id,
                    'name' => $name,
                    'status' => DialerLeadStatus::Pending,
                ],
            );

            if ($lead->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->leadImportForm->fill([
            'dialer_campaign_id' => $campaign->id,
            'numbers' => null,
        ]);

        Notification::make()->title("{$created} leads imported")->success()->send();
    }

    public function startCampaign(int $campaignId, DialerCampaignService $service): void
    {
        $campaign = $this->campaignQuery()->findOrFail($campaignId);
        $queued = $service->startCampaign($campaign);

        Notification::make()->title("Campaign started ({$queued} queued)")->success()->send();
    }

    public function pauseCampaign(int $campaignId, DialerCampaignService $service): void
    {
        $campaign = $this->campaignQuery()->findOrFail($campaignId);
        $service->pauseCampaign($campaign);

        Notification::make()->title('Campaign paused')->warning()->send();
    }

    public function stopCampaign(int $campaignId, DialerCampaignService $service): void
    {
        $campaign = $this->campaignQuery()->findOrFail($campaignId);
        $service->stopCampaign($campaign);

        Notification::make()->title('Campaign stopped')->danger()->send();
    }

    public function queueNow(int $campaignId, DialerCampaignService $service): void
    {
        $campaign = $this->campaignQuery()->findOrFail($campaignId);
        $queued = $service->queueNextBatch($campaign);

        Notification::make()->title("Queued {$queued} leads")->success()->send();
    }

    public function getCampaigns()
    {
        return $this->campaignQuery()
            ->withCount([
                'leads',
                'leads as pending_leads_count' => fn ($query) => $query->where('status', DialerLeadStatus::Pending->value),
                'attempts as active_attempts_count' => fn ($query) => $query->whereIn('status', [DialerAttemptStatus::Queued->value, DialerAttemptStatus::Sent->value, DialerAttemptStatus::Ringing->value]),
            ])
            ->latest('id')
            ->limit(10)
            ->get();
    }

    public function getRecentAttempts()
    {
        return DialerCallAttempt::query()
            ->with(['lead', 'campaign'])
            ->latest('id')
            ->limit(20)
            ->get();
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getCampaignOptions(): array
    {
        return $this->campaignQuery()
            ->latest('id')
            ->pluck('name', 'id')
            ->all();
    }

    protected function campaignQuery()
    {
        $tenant = Filament::getTenant();

        return DialerCampaign::query()
            ->when($tenant !== null, fn ($query) => $query->where('team_id', $tenant->id));
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) DialerCampaign::query()->where('status', DialerCampaignStatus::Running->value)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
