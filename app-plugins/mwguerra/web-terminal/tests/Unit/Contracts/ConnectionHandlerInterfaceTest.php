<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Contracts\ConnectionHandlerInterface;
use MWGuerra\WebTerminal\Data\CommandResult;
use MWGuerra\WebTerminal\Data\ConnectionConfig;

describe('ConnectionHandlerInterface', function () {
    it('defines required methods', function () {
        $reflection = new ReflectionClass(ConnectionHandlerInterface::class);

        expect($reflection->isInterface())->toBeTrue();
        expect($reflection->hasMethod('connect'))->toBeTrue();
        expect($reflection->hasMethod('execute'))->toBeTrue();
        expect($reflection->hasMethod('disconnect'))->toBeTrue();
        expect($reflection->hasMethod('isConnected'))->toBeTrue();
        expect($reflection->hasMethod('getConfig'))->toBeTrue();
        expect($reflection->hasMethod('getTimeout'))->toBeTrue();
        expect($reflection->hasMethod('setTimeout'))->toBeTrue();
        expect($reflection->hasMethod('getWorkingDirectory'))->toBeTrue();
        expect($reflection->hasMethod('setWorkingDirectory'))->toBeTrue();
    });

    it('has correct method signatures', function () {
        $reflection = new ReflectionClass(ConnectionHandlerInterface::class);

        // connect(ConnectionConfig $config): void
        $connect = $reflection->getMethod('connect');
        expect($connect->getParameters())->toHaveCount(1);
        expect($connect->getParameters()[0]->getName())->toBe('config');
        expect($connect->getParameters()[0]->getType()?->getName())->toBe(ConnectionConfig::class);
        expect($connect->getReturnType()?->getName())->toBe('void');

        // execute(string $command, ?float $timeout = null): CommandResult
        $execute = $reflection->getMethod('execute');
        expect($execute->getParameters())->toHaveCount(2);
        expect($execute->getParameters()[0]->getName())->toBe('command');
        expect($execute->getParameters()[0]->getType()?->getName())->toBe('string');
        expect($execute->getParameters()[1]->getName())->toBe('timeout');
        expect($execute->getParameters()[1]->allowsNull())->toBeTrue();
        expect($execute->getReturnType()?->getName())->toBe(CommandResult::class);

        // disconnect(): void
        $disconnect = $reflection->getMethod('disconnect');
        expect($disconnect->getParameters())->toBeEmpty();
        expect($disconnect->getReturnType()?->getName())->toBe('void');

        // isConnected(): bool
        $isConnected = $reflection->getMethod('isConnected');
        expect($isConnected->getParameters())->toBeEmpty();
        expect($isConnected->getReturnType()?->getName())->toBe('bool');
    });

    it('has configuration getters and setters with correct types', function () {
        $reflection = new ReflectionClass(ConnectionHandlerInterface::class);

        // getConfig(): ?ConnectionConfig
        $getConfig = $reflection->getMethod('getConfig');
        expect($getConfig->getReturnType()?->allowsNull())->toBeTrue();
        expect($getConfig->getReturnType()?->getName())->toBe(ConnectionConfig::class);

        // getTimeout(): float
        $getTimeout = $reflection->getMethod('getTimeout');
        expect($getTimeout->getReturnType()?->getName())->toBe('float');

        // setTimeout(float $timeout): static
        $setTimeout = $reflection->getMethod('setTimeout');
        expect($setTimeout->getParameters()[0]->getType()?->getName())->toBe('float');
        expect($setTimeout->getReturnType()?->getName())->toBe('static');

        // getWorkingDirectory(): ?string
        $getWd = $reflection->getMethod('getWorkingDirectory');
        expect($getWd->getReturnType()?->allowsNull())->toBeTrue();
        expect($getWd->getReturnType()?->getName())->toBe('string');

        // setWorkingDirectory(?string $directory): static
        $setWd = $reflection->getMethod('setWorkingDirectory');
        expect($setWd->getParameters()[0]->allowsNull())->toBeTrue();
        expect($setWd->getReturnType()?->getName())->toBe('static');
    });
});
