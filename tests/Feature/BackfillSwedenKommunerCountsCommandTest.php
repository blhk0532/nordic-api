<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('backfills kommuner gator and adresser counts from child tables', function () {
    $stockholmId = DB::table('sweden_kommuner')->insertGetId([
        'kommun' => 'Stockholm',
        'gator' => null,
        'adresser' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $uppsalaId = DB::table('sweden_kommuner')->insertGetId([
        'kommun' => 'Uppsala',
        'gator' => null,
        'adresser' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sweden_gator')->insert([
        ['gata' => 'A', 'kommun' => 'Stockholm', 'created_at' => now(), 'updated_at' => now()],
        ['gata' => 'B', 'kommun' => ' stockholm ', 'created_at' => now(), 'updated_at' => now()],
        ['gata' => 'C', 'kommun' => 'Uppsala', 'created_at' => now(), 'updated_at' => now()],
        ['gata' => 'D', 'kommun' => 'Stockholm', 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => now()],
    ]);

    DB::table('sweden_adresser')->insert([
        ['adress' => 'A 1', 'kommun' => 'Stockholm', 'created_at' => now(), 'updated_at' => now()],
        ['adress' => 'A 2', 'kommun' => 'STOCKHOLM', 'created_at' => now(), 'updated_at' => now()],
        ['adress' => 'U 1', 'kommun' => 'Uppsala', 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => now()],
    ]);

    $this->artisan('app:backfill-sweden-kommuner-counts')
        ->assertSuccessful();

    expect((int) DB::table('sweden_kommuner')->where('id', $stockholmId)->value('gator'))->toBe(2);
    expect((int) DB::table('sweden_kommuner')->where('id', $stockholmId)->value('adresser'))->toBe(2);

    expect((int) DB::table('sweden_kommuner')->where('id', $uppsalaId)->value('gator'))->toBe(1);
    expect((int) DB::table('sweden_kommuner')->where('id', $uppsalaId)->value('adresser'))->toBe(0);
});

it('respects only-missing option', function () {
    $stockholmId = DB::table('sweden_kommuner')->insertGetId([
        'kommun' => 'Stockholm',
        'gator' => 99,
        'adresser' => 88,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sweden_gator')->insert([
        ['gata' => 'A', 'kommun' => 'Stockholm', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::table('sweden_adresser')->insert([
        ['adress' => 'A 1', 'kommun' => 'Stockholm', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->artisan('app:backfill-sweden-kommuner-counts --only-missing')
        ->assertSuccessful();

    expect((int) DB::table('sweden_kommuner')->where('id', $stockholmId)->value('gator'))->toBe(99);
    expect((int) DB::table('sweden_kommuner')->where('id', $stockholmId)->value('adresser'))->toBe(88);
});
