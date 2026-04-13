<?php

use App\Models\Merinfo;
use App\Models\SwedenPersoner;

it('imports sweden personer records from the merinfos table', function () {
    Merinfo::create([
        'type' => 'person',
        'short_uuid' => 'mer-source-test',
        'name' => 'Anna Andersson',
        'givenNameOrFirstName' => 'Anna',
        'personalNumber' => '19900101-1234',
        'pnr' => [],
        'address' => [
            'street' => 'Testgatan 1',
            'zip_code' => '12345',
            'city' => 'Stockholm',
        ],
        'gender' => 'female',
        'is_celebrity' => false,
        'has_company_engagement' => false,
        'is_house' => false,
        'number_plus_count' => 1,
        'phone_number' => [
            ['number' => '0701234567', 'raw' => '0701234567'],
        ],
        'url' => 'https://merinfo.se/person/anna-andersson',
        'same_address_url' => 'https://merinfo.se/address/testgatan-1',
    ]);

    $this->artisan('import:sweden-personer', [
        '--source' => 'mer',
        '--chunk' => 10,
    ])->assertExitCode(0);

    expect(SwedenPersoner::query()->count())->toBe(1);

    $this->assertDatabaseHas('sweden_personer', [
        'personnamn' => 'Anna Andersson',
        'fornamn' => 'Anna',
        'efternamn' => 'Andersson',
        'personnummer' => '19900101-1234',
        'adress' => 'Testgatan 1',
        'postnummer' => '12345',
        'postort' => 'Stockholm',
        'merinfo_link' => 'https://merinfo.se/person/anna-andersson',
    ]);
});
