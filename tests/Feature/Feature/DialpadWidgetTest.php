<?php

use App\Enums\DialerCampaignStatus;
use App\Filament\Widgets\DialpadWidget;
use App\Jobs\OriginateAmiCallJob;
use App\Models\DialerCampaign;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('dialpad appends and clears digits', function () {
    Livewire::test(DialpadWidget::class)
        ->assertSeeHtml('wire:click="placeCall"')
        ->call('appendDigit', '4')
        ->call('appendDigit', '6')
        ->assertSet('number', '46')
        ->call('backspace')
        ->assertSet('number', '4')
        ->call('clear')
        ->assertSet('number', '');
});

test('dialpad queues manual call for selected campaign', function () {
    Queue::fake();

    $campaign = DialerCampaign::factory()->create([
        'status' => DialerCampaignStatus::Running,
        'source_channel' => 'SIP/1001',
        'context' => 'default',
        'caller_id' => 'Dialer',
    ]);

    Livewire::test(DialpadWidget::class)
        ->set('campaignId', $campaign->id)
        ->set('number', '46701112233')
        ->call('placeCall');

    Queue::assertPushed(OriginateAmiCallJob::class, 1);
});
