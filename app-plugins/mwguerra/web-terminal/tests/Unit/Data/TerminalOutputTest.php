<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Data\CommandResult;
use MWGuerra\WebTerminal\Data\TerminalOutput;
use MWGuerra\WebTerminal\Enums\OutputType;

describe('TerminalOutput', function () {
    describe('construction', function () {
        it('can be instantiated with all properties', function () {
            $output = new TerminalOutput(
                type: OutputType::Stdout,
                content: 'Hello World',
            );

            expect($output->type)->toBe(OutputType::Stdout)
                ->and($output->content)->toBe('Hello World')
                ->and($output->timestamp)->toBeInstanceOf(DateTimeImmutable::class);
        });
    });

    describe('factory methods', function () {
        it('can create stdout output', function () {
            $output = TerminalOutput::stdout('output text');

            expect($output->type)->toBe(OutputType::Stdout)
                ->and($output->content)->toBe('output text');
        });

        it('can create stderr output', function () {
            $output = TerminalOutput::stderr('error text');

            expect($output->type)->toBe(OutputType::Stderr)
                ->and($output->content)->toBe('error text');
        });

        it('can create error output', function () {
            $output = TerminalOutput::error('error message');

            expect($output->type)->toBe(OutputType::Error)
                ->and($output->content)->toBe('error message');
        });

        it('can create info output', function () {
            $output = TerminalOutput::info('info message');

            expect($output->type)->toBe(OutputType::Info)
                ->and($output->content)->toBe('info message');
        });

        it('can create command output', function () {
            $output = TerminalOutput::command('ls -la');

            expect($output->type)->toBe(OutputType::Command)
                ->and($output->content)->toBe('ls -la');
        });

        it('can create system output', function () {
            $output = TerminalOutput::system('Connection established');

            expect($output->type)->toBe(OutputType::System)
                ->and($output->content)->toBe('Connection established');
        });
    });

    describe('fromCommandResult', function () {
        it('creates outputs from command result with command and stdout', function () {
            $result = CommandResult::success(
                stdout: 'file1.txt',
                executionTime: 0.1,
                command: 'ls',
            );

            $outputs = TerminalOutput::fromCommandResult($result);

            expect($outputs)->toHaveCount(2)
                ->and($outputs[0]->type)->toBe(OutputType::Command)
                ->and($outputs[0]->content)->toBe('ls')
                ->and($outputs[1]->type)->toBe(OutputType::Stdout)
                ->and($outputs[1]->content)->toBe('file1.txt');
        });

        it('creates outputs from command result with stderr', function () {
            $result = CommandResult::failure(
                stderr: 'Permission denied',
                exitCode: 1,
                executionTime: 0.1,
                command: 'rm /root/file',
            );

            $outputs = TerminalOutput::fromCommandResult($result);

            expect($outputs)->toHaveCount(2)
                ->and($outputs[0]->type)->toBe(OutputType::Command)
                ->and($outputs[1]->type)->toBe(OutputType::Stderr);
        });

        it('handles empty command', function () {
            $result = CommandResult::success(stdout: 'output', executionTime: 0.1);

            $outputs = TerminalOutput::fromCommandResult($result);

            expect($outputs)->toHaveCount(1)
                ->and($outputs[0]->type)->toBe(OutputType::Stdout);
        });

        it('handles empty output', function () {
            $result = new CommandResult(
                stdout: '',
                stderr: '',
                exitCode: 0,
                executionTime: 0.1,
                command: 'true',
            );

            $outputs = TerminalOutput::fromCommandResult($result);

            expect($outputs)->toHaveCount(1)
                ->and($outputs[0]->type)->toBe(OutputType::Command);
        });
    });

    describe('type checking', function () {
        it('identifies error outputs', function () {
            expect(TerminalOutput::stderr('error')->isError())->toBeTrue();
            expect(TerminalOutput::error('error')->isError())->toBeTrue();
            expect(TerminalOutput::stdout('output')->isError())->toBeFalse();
            expect(TerminalOutput::info('info')->isError())->toBeFalse();
        });

        it('identifies stdout outputs', function () {
            expect(TerminalOutput::stdout('output')->isStdout())->toBeTrue();
            expect(TerminalOutput::stderr('error')->isStdout())->toBeFalse();
        });

        it('identifies info outputs', function () {
            expect(TerminalOutput::info('info')->isInfo())->toBeTrue();
            expect(TerminalOutput::system('system')->isInfo())->toBeTrue();
            expect(TerminalOutput::stdout('output')->isInfo())->toBeFalse();
        });
    });

    describe('content handling', function () {
        it('returns CSS class', function () {
            $output = TerminalOutput::stdout('text');

            expect($output->cssClass())->toBe('terminal-stdout');
        });

        it('formats timestamp', function () {
            $output = TerminalOutput::stdout('text');

            expect($output->formattedTimestamp())->toMatch('/^\d{2}:\d{2}:\d{2}$/');
        });

        it('splits content into lines', function () {
            $output = TerminalOutput::stdout("line1\nline2\nline3");

            expect($output->lines())->toBe(['line1', 'line2', 'line3']);
        });

        it('returns empty array for empty content', function () {
            $output = TerminalOutput::stdout('');

            expect($output->lines())->toBe([]);
        });

        it('checks if empty', function () {
            expect(TerminalOutput::stdout('')->isEmpty())->toBeTrue();
            expect(TerminalOutput::stdout('text')->isEmpty())->toBeFalse();
        });
    });

    describe('toArray', function () {
        it('converts to array representation', function () {
            $output = TerminalOutput::stdout('Hello');

            $array = $output->toArray();

            expect($array)->toHaveKeys(['type', 'content', 'timestamp', 'css_class'])
                ->and($array['type'])->toBe('stdout')
                ->and($array['content'])->toBe('Hello')
                ->and($array['css_class'])->toBe('terminal-stdout');
        });
    });

    describe('immutability', function () {
        it('is a readonly class', function () {
            $output = TerminalOutput::stdout('text');

            $reflection = new ReflectionClass($output);

            expect($reflection->isReadOnly())->toBeTrue();
        });
    });
});
