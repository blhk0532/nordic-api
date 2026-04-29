<?php

declare(strict_types=1);

use App\Models\SwedenPersoner;

it('updates hitta person without failing when telefonnummer contains nested arrays', function () {
    $person = SwedenPersoner::create([
        'adress' => 'Testgatan 1',
        'postnummer' => '11122',
        'postort' => 'Stockholm',
        'fornamn' => 'Anna',
        'efternamn' => 'Andersson',
        'telefon' => null,
        'telefonnummer' => ['0701111111'],
    ]);

    $payload = [
        'adress' => 'Testgatan 1',
        'postnummer' => '11122',
        'postort' => 'Stockholm',
        'fornamn' => 'Anna',
        'efternamn' => 'Andersson',
        'telefonnummer' => ['0702222222', ['bad' => 'value'], null, 'abc'],
        'hitta_data' => ['source' => 'test'],
    ];

    $response = $this->postJson('/api/sweden-personer/hitta', $payload);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('action', 'updated');

    $person->refresh();

    expect($person->telefonnummer)->toBeArray();

    $numbers = $person->telefonnummer;
    sort($numbers);

    expect($numbers)->toBe(['0701111111', '0702222222']);
    expect($person->telefon)->toBe('0702222222');
});
