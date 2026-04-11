<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Data\ConnectionConfig;
use MWGuerra\WebTerminal\Enums\ConnectionType;
use MWGuerra\WebTerminal\Exceptions\ConnectionException;

describe('ConnectionException', function () {
    it('can be instantiated with message only', function () {
        $exception = new ConnectionException('Test error');

        expect($exception->getMessage())->toBe('Test error');
        expect($exception->connectionType)->toBeNull();
        expect($exception->host)->toBeNull();
    });

    it('can store connection type and host', function () {
        $exception = new ConnectionException(
            message: 'Connection failed',
            connectionType: ConnectionType::SSH,
            host: 'example.com',
        );

        expect($exception->connectionType)->toBe(ConnectionType::SSH);
        expect($exception->host)->toBe('example.com');
    });

    it('can chain from previous exception', function () {
        $previous = new Exception('Root cause');
        $exception = new ConnectionException(
            message: 'Wrapped error',
            previous: $previous,
        );

        expect($exception->getPrevious())->toBe($previous);
    });

    describe('connectionFailed factory', function () {
        it('creates exception with host', function () {
            $config = ConnectionConfig::sshWithPassword(
                host: 'server.example.com',
                username: 'user',
                password: 'pass',
            );

            $exception = ConnectionException::connectionFailed($config, 'Network unreachable');

            expect($exception->getMessage())->toContain('SSH');
            expect($exception->getMessage())->toContain('server.example.com');
            expect($exception->getMessage())->toContain('Network unreachable');
            expect($exception->connectionType)->toBe(ConnectionType::SSH);
            expect($exception->host)->toBe('server.example.com');
        });

        it('creates exception for local connection', function () {
            $config = ConnectionConfig::local();

            $exception = ConnectionException::connectionFailed($config);

            expect($exception->getMessage())->toContain('Local');
            expect($exception->connectionType)->toBe(ConnectionType::Local);
            expect($exception->host)->toBeNull();
        });

        it('includes previous exception', function () {
            $config = ConnectionConfig::local();
            $previous = new RuntimeException('Root cause');

            $exception = ConnectionException::connectionFailed($config, '', $previous);

            expect($exception->getPrevious())->toBe($previous);
        });
    });

    describe('notConnected factory', function () {
        it('creates exception with helpful message', function () {
            $exception = ConnectionException::notConnected();

            expect($exception->getMessage())->toContain('Not connected');
            expect($exception->getMessage())->toContain('connect()');
        });
    });

    describe('executionFailed factory', function () {
        it('creates exception with command and reason', function () {
            $exception = ConnectionException::executionFailed(
                command: 'ls -la',
                reason: 'Process killed',
                type: ConnectionType::Local,
            );

            expect($exception->getMessage())->toContain('ls -la');
            expect($exception->getMessage())->toContain('Process killed');
            expect($exception->connectionType)->toBe(ConnectionType::Local);
        });

        it('creates exception without reason', function () {
            $exception = ConnectionException::executionFailed(
                command: 'whoami',
                reason: '',
            );

            expect($exception->getMessage())->toContain('whoami');
            expect($exception->getMessage())->not->toContain(':');
        });
    });

    describe('authenticationFailed factory', function () {
        it('creates exception with full details', function () {
            $config = ConnectionConfig::sshWithPassword(
                host: 'secure.example.com',
                username: 'admin',
                password: 'wrong',
            );

            $exception = ConnectionException::authenticationFailed($config);

            expect($exception->getMessage())->toContain('Authentication failed');
            expect($exception->getMessage())->toContain('SSH');
            expect($exception->getMessage())->toContain('secure.example.com');
            expect($exception->getMessage())->toContain('admin');
            expect($exception->connectionType)->toBe(ConnectionType::SSH);
            expect($exception->host)->toBe('secure.example.com');
        });
    });

    describe('timeout factory', function () {
        it('creates exception with command and timeout', function () {
            $exception = ConnectionException::timeout(
                command: 'slow-command',
                timeoutSeconds: 30.5,
                type: ConnectionType::SSH,
            );

            expect($exception->getMessage())->toContain('slow-command');
            expect($exception->getMessage())->toContain('30.5');
            expect($exception->getMessage())->toContain('timed out');
            expect($exception->connectionType)->toBe(ConnectionType::SSH);
        });
    });

    describe('invalidConfig factory', function () {
        it('creates exception with reason', function () {
            $exception = ConnectionException::invalidConfig(
                reason: 'Missing host for SSH connection',
                type: ConnectionType::SSH,
            );

            expect($exception->getMessage())->toContain('Invalid connection configuration');
            expect($exception->getMessage())->toContain('Missing host');
            expect($exception->connectionType)->toBe(ConnectionType::SSH);
        });
    });

    describe('disconnectionFailed factory', function () {
        it('creates exception with reason', function () {
            $exception = ConnectionException::disconnectionFailed(
                reason: 'Connection already closed',
                type: ConnectionType::SSH,
            );

            expect($exception->getMessage())->toContain('Failed to disconnect');
            expect($exception->getMessage())->toContain('Connection already closed');
            expect($exception->connectionType)->toBe(ConnectionType::SSH);
        });

        it('creates exception without reason', function () {
            $exception = ConnectionException::disconnectionFailed();

            expect($exception->getMessage())->toBe('Failed to disconnect');
        });
    });

    it('is an Exception', function () {
        $exception = new ConnectionException('Test');

        expect($exception)->toBeInstanceOf(Exception::class);
    });
});
