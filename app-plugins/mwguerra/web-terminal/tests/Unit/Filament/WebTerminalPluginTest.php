<?php

declare(strict_types=1);

use Filament\Contracts\Plugin;
use MWGuerra\WebTerminal\WebTerminalPlugin;

// Skip all tests if Filament is not installed
beforeEach(function () {
    if (! interface_exists(Plugin::class)) {
        $this->markTestSkipped('Filament is not installed. These tests require filament/filament package.');
    }
});

describe('WebTerminalPlugin', function () {
    it('can be instantiated using make()', function () {
        $plugin = WebTerminalPlugin::make();

        expect($plugin)->toBeInstanceOf(WebTerminalPlugin::class);
    });

    it('has correct plugin id', function () {
        $plugin = WebTerminalPlugin::make();

        expect($plugin->getId())->toBe('web-terminal');
    });

    it('is enabled by default', function () {
        $plugin = WebTerminalPlugin::make();

        expect($plugin->isEnabled())->toBeTrue();
    });

    it('can be disabled', function () {
        $plugin = WebTerminalPlugin::make()->disabled();

        expect($plugin->isEnabled())->toBeFalse();
    });

    it('can be enabled after being disabled', function () {
        $plugin = WebTerminalPlugin::make()
            ->disabled()
            ->enabled();

        expect($plugin->isEnabled())->toBeTrue();
    });

    it('can set allowed commands', function () {
        $commands = ['ls', 'pwd', 'whoami'];

        $plugin = WebTerminalPlugin::make()
            ->allowedCommands($commands);

        expect($plugin->getAllowedCommands())->toBe($commands);
    });

    it('can set connection type', function () {
        $plugin = WebTerminalPlugin::make()
            ->connectionType('local');

        expect($plugin->getConnectionType())->toBe('local');
    });

    it('can set connection config', function () {
        $config = [
            'host' => 'localhost',
            'port' => 22,
        ];

        $plugin = WebTerminalPlugin::make()
            ->connectionConfig($config);

        expect($plugin->getConnectionConfig())->toBe($config);
    });

    it('supports fluent configuration', function () {
        $plugin = WebTerminalPlugin::make()
            ->enabled()
            ->allowedCommands(['ls', 'pwd'])
            ->connectionType('ssh')
            ->connectionConfig(['host' => 'example.com']);

        expect($plugin)
            ->isEnabled()->toBeTrue()
            ->getAllowedCommands()->toBe(['ls', 'pwd'])
            ->getConnectionType()->toBe('ssh')
            ->getConnectionConfig()->toBe(['host' => 'example.com']);
    });
});
