<?php

use App\Enums\DialerCampaignStatus;
use App\Filament\Pages\DialerControl;
use App\Models\DialerCampaign;
use Illuminate\Support\Facades\Schema;

test('navigation badge returns null when dialer campaigns table is missing', function () {
    Schema::rename('dialer_campaigns', 'dialer_campaigns_backup');

    try {
        expect(DialerControl::getNavigationBadge())->toBeNull();
    } finally {
        Schema::rename('dialer_campaigns_backup', 'dialer_campaigns');
    }
});

test('navigation badge returns running campaign count', function () {
    DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Running,
    ]);

    DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Running,
    ]);

    DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Draft,
    ]);

    expect(DialerControl::getNavigationBadge())->toBe('2');
});
