<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Data\CommandResult;
use MWGuerra\WebTerminal\Data\ConnectionConfig;
use MWGuerra\WebTerminal\Enums\ConnectionType;
use MWGuerra\WebTerminal\Events\CommandExecutedEvent;

describe('CommandExecutedEvent', function () {
    describe('construction', function () {
        it('can be created with all parameters', function () {
            $result = new CommandResult(
                stdout: 'output',
                stderr: '',
                exitCode: 0,
                executionTime: 0.5,
            );

            $event = new CommandExecutedEvent(
                command: 'ls -la',
                result: $result,
                connectionType: ConnectionType::Local,
                userId: 'user-123',
                sessionId: 'session-456',
                ipAddress: '192.168.1.1',
                metadata: ['key' => 'value'],
            );

            expect($event->command)->toBe('ls -la');
            expect($event->result)->toBe($result);
            expect($event->connectionType)->toBe(ConnectionType::Local);
            expect($event->userId)->toBe('user-123');
            expect($event->sessionId)->toBe('session-456');
            expect($event->ipAddress)->toBe('192.168.1.1');
            expect($event->metadata)->toBe(['key' => 'value']);
        });

        it('can be created with minimal parameters', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'pwd',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->userId)->toBeNull();
            expect($event->sessionId)->toBeNull();
            expect($event->ipAddress)->toBeNull();
            expect($event->metadata)->toBe([]);
        });
    });

    describe('fromExecution', function () {
        it('creates event from execution details', function () {
            $result = new CommandResult(
                stdout: 'output',
                stderr: '',
                exitCode: 0,
                executionTime: 0.5,
            );

            $config = ConnectionConfig::local();

            $event = CommandExecutedEvent::fromExecution(
                command: 'ls -la',
                result: $result,
                config: $config,
                userId: 'user-123',
            );

            expect($event->command)->toBe('ls -la');
            expect($event->connectionType)->toBe(ConnectionType::Local);
            expect($event->userId)->toBe('user-123');
        });

        it('includes connection metadata', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $config = ConnectionConfig::sshWithPassword(
                host: 'example.com',
                username: 'admin',
                password: 'secret',
            );

            $event = CommandExecutedEvent::fromExecution(
                command: 'uptime',
                result: $result,
                config: $config,
            );

            expect($event->metadata)->toHaveKey('host');
            expect($event->metadata)->toHaveKey('username');
            expect($event->metadata['host'])->toBe('example.com');
            expect($event->metadata['username'])->toBe('admin');
        });

        it('merges custom metadata', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $config = ConnectionConfig::local();

            $event = CommandExecutedEvent::fromExecution(
                command: 'pwd',
                result: $result,
                config: $config,
                metadata: ['custom' => 'data'],
            );

            expect($event->metadata)->toHaveKey('custom');
            expect($event->metadata['custom'])->toBe('data');
        });
    });

    describe('status checks', function () {
        it('identifies successful execution', function () {
            $result = new CommandResult(
                stdout: 'success',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->wasSuccessful())->toBeTrue();
            expect($event->wasFailed())->toBeFalse();
            expect($event->wasTimeout())->toBeFalse();
        });

        it('identifies failed execution', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: 'error',
                exitCode: 1,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->wasSuccessful())->toBeFalse();
            expect($event->wasFailed())->toBeTrue();
        });

        it('identifies timeout', function () {
            $result = CommandResult::timeout(
                timeoutSeconds: 10.0,
                command: 'long-command',
            );

            $event = new CommandExecutedEvent(
                command: 'long-command',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->wasTimeout())->toBeTrue();
        });
    });

    describe('getters', function () {
        it('returns exit code', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 42,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->getExitCode())->toBe(42);
        });

        it('returns execution time', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 1.234,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->getExecutionTime())->toBe(1.234);
        });
    });

    describe('toArray', function () {
        it('returns all event data as array', function () {
            $result = new CommandResult(
                stdout: 'output',
                stderr: '',
                exitCode: 0,
                executionTime: 0.5,
            );

            $event = new CommandExecutedEvent(
                command: 'ls -la',
                result: $result,
                connectionType: ConnectionType::SSH,
                userId: 'user-123',
                sessionId: 'session-456',
                ipAddress: '192.168.1.1',
                metadata: ['key' => 'value'],
            );

            $array = $event->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKeys([
                'command',
                'connection_type',
                'exit_code',
                'execution_time',
                'success',
                'timeout',
                'user_id',
                'session_id',
                'ip_address',
                'metadata',
                'timestamp',
            ]);
            expect($array['command'])->toBe('ls -la');
            expect($array['connection_type'])->toBe('ssh');
            expect($array['exit_code'])->toBe(0);
            expect($array['success'])->toBeTrue();
        });

        it('includes timestamp', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            $array = $event->toArray();

            expect($array['timestamp'])->toBeString();
            expect($array['timestamp'])->toContain('T'); // ISO 8601 format
        });
    });

    describe('toSanitizedArray', function () {
        it('excludes stdout and stderr', function () {
            $result = new CommandResult(
                stdout: 'sensitive output',
                stderr: 'sensitive error',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            $array = $event->toSanitizedArray();

            expect($array)->not->toHaveKey('stdout');
            expect($array)->not->toHaveKey('stderr');
        });

        it('truncates long commands', function () {
            $longCommand = str_repeat('a', 300);

            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: $longCommand,
                result: $result,
                connectionType: ConnectionType::Local,
            );

            $array = $event->toSanitizedArray();

            expect(strlen($array['command']))->toBe(203); // 200 chars + '...'
            expect($array['command'])->toEndWith('...');
        });

        it('preserves short commands', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'ls -la',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            $array = $event->toSanitizedArray();

            expect($array['command'])->toBe('ls -la');
        });
    });

    describe('connection types', function () {
        it('works with Local connection', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::Local,
            );

            expect($event->connectionType)->toBe(ConnectionType::Local);
        });

        it('works with SSH connection', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
            );

            $event = new CommandExecutedEvent(
                command: 'test',
                result: $result,
                connectionType: ConnectionType::SSH,
            );

            expect($event->connectionType)->toBe(ConnectionType::SSH);
        });
    });
});
