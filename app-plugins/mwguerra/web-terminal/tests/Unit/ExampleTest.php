<?php

declare(strict_types=1);

it('can run a basic test', function () {
    expect(true)->toBeTrue();
});

it('can load the package configuration', function () {
    expect(config('web-terminal.timeout'))->toBe(10);
});
