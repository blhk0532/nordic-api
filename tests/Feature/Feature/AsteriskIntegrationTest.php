<?php

use App\Jobs\OriginateAmiCallJob;
use App\Services\AsteriskDialerService;
use Illuminate\Support\Facades\Artisan;

test('asterisk ping command reports success', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('ami:action', ['action' => 'Ping'])
        ->andReturn(0);

    $dialerService = new AsteriskDialerService;

    expect($dialerService->ping())->toBeTrue();
});

test('dialer service sends originate action with expected payload', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('ami:action', Mockery::on(function (array $payload): bool {
            $arguments = $payload['--arguments'] ?? [];

            return ($payload['action'] ?? null) === 'Originate'
                && ($arguments['Channel'] ?? null) === 'SIP/1001'
                && ($arguments['Context'] ?? null) === 'default'
                && ($arguments['Exten'] ?? null) === '1002'
                && ($arguments['Priority'] ?? null) === '1'
                && ($arguments['CallerID'] ?? null) === '1001'
                && ($arguments['Timeout'] ?? null) === '30000'
                && ($arguments['Async'] ?? null) === 'true'
                && isset($arguments['ActionID'])
                && ($arguments['Variable'] ?? null) === 'campaign=SpringSale|lead_id=42';
        }))
        ->andReturn(0);

    $dialerService = new AsteriskDialerService;

    $didOriginate = $dialerService->originate(
        channel: 'SIP/1001',
        extension: '1002',
        variables: [
            'campaign' => 'SpringSale',
            'lead_id' => '42',
        ],
        callerId: '1001',
    );

    expect($didOriginate)->toBeTrue();
});

test('originate job calls dialer service', function () {
    $dialerService = Mockery::mock(AsteriskDialerService::class);
    $dialerService->shouldReceive('originate')
        ->once()
        ->with('SIP/1001', '1002', 'default', 1, ['campaign' => 'SpringSale'], '1001', 30000, null)
        ->andReturn(true);

    app()->instance(AsteriskDialerService::class, $dialerService);

    $job = new OriginateAmiCallJob(
        channel: 'SIP/1001',
        extension: '1002',
        variables: ['campaign' => 'SpringSale'],
        callerId: '1001',
    );

    app()->call([$job, 'handle']);
});
