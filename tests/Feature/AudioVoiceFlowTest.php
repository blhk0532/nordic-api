<?php

use App\Models\AudioVoiceFlow;
use App\Models\User;

test('can create audio voice flow record', function () {
    $user = User::factory()->create();

    $audio = AudioVoiceFlow::factory()->create([
        'user_id' => $user->id,
        'name' => 'Sales Pitch v1',
        'status' => 'active',
    ]);

    expect($audio)->toBeInstanceOf(AudioVoiceFlow::class)
        ->and($audio->name)->toBe('Sales Pitch v1')
        ->and($audio->status)->toBe('active')
        ->and($audio->user_id)->toBe($user->id);
});

test('can filter audio files by status', function () {
    User::factory()->create(); // Ensure a user exists

    AudioVoiceFlow::factory(5)->create(['status' => 'active']);
    AudioVoiceFlow::factory(3)->create(['status' => 'draft']);
    AudioVoiceFlow::factory(2)->create(['status' => 'approved']);

    expect(AudioVoiceFlow::where('status', 'active')->count())->toBe(5)
        ->and(AudioVoiceFlow::where('status', 'draft')->count())->toBe(3)
        ->and(AudioVoiceFlow::where('status', 'approved')->count())->toBe(2);
});

test('can order audio queue by priority', function () {
    User::factory()->create();

    $low = AudioVoiceFlow::factory()->create(['priority' => 100]);
    $high = AudioVoiceFlow::factory()->create(['priority' => 1]);
    $medium = AudioVoiceFlow::factory()->create(['priority' => 50]);

    $queue = AudioVoiceFlow::orderBy('priority', 'asc')->get();

    expect($queue->first()->id)->toBe($high->id)
        ->and($queue[1]->id)->toBe($medium->id)
        ->and($queue->last()->id)->toBe($low->id);
});

test('can track play count', function () {
    User::factory()->create();

    $audio = AudioVoiceFlow::factory()->create(['play_count' => 0]);

    expect($audio->play_count)->toBe(0);

    $audio->increment('play_count');

    expect($audio->refresh()->play_count)->toBe(1);
});

test('can store tags as json', function () {
    User::factory()->create();

    $audio = AudioVoiceFlow::factory()->create([
        'tags' => ['telemarketing', 'sales', 'outbound'],
    ]);

    expect($audio->tags)->toBeArray()
        ->and($audio->tags)->toContain('telemarketing', 'sales');
});

test('soft deletes are enabled', function () {
    User::factory()->create();

    $audio = AudioVoiceFlow::factory()->create();

    $audio->delete();

    expect(AudioVoiceFlow::withTrashed()->count())->toBe(1)
        ->and(AudioVoiceFlow::count())->toBe(0);
});

test('can use scopes for status filtering', function () {
    User::factory()->create();

    AudioVoiceFlow::factory()->create(['status' => 'active']);
    AudioVoiceFlow::factory()->create(['status' => 'draft']);

    expect(AudioVoiceFlow::active()->count())->toBe(1)
        ->and(AudioVoiceFlow::draft()->count())->toBe(1);
});
