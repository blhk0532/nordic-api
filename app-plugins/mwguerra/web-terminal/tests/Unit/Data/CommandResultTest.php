<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Data\CommandResult;

describe('CommandResult', function () {
    describe('construction', function () {
        it('can be instantiated with all properties', function () {
            $result = new CommandResult(
                stdout: 'Hello World',
                stderr: '',
                exitCode: 0,
                executionTime: 0.5,
                command: 'echo "Hello World"',
            );

            expect($result->stdout)->toBe('Hello World')
                ->and($result->stderr)->toBe('')
                ->and($result->exitCode)->toBe(0)
                ->and($result->executionTime)->toBe(0.5)
                ->and($result->command)->toBe('echo "Hello World"')
                ->and($result->executedAt)->toBeInstanceOf(DateTimeImmutable::class);
        });
    });

    describe('factory methods', function () {
        it('can create success result', function () {
            $result = CommandResult::success(
                stdout: 'output',
                executionTime: 0.1,
                command: 'ls',
            );

            expect($result->isSuccessful())->toBeTrue()
                ->and($result->exitCode)->toBe(0)
                ->and($result->stderr)->toBe('');
        });

        it('can create failure result', function () {
            $result = CommandResult::failure(
                stderr: 'error message',
                exitCode: 1,
                executionTime: 0.2,
                command: 'failing-command',
            );

            expect($result->isFailed())->toBeTrue()
                ->and($result->exitCode)->toBe(1)
                ->and($result->stderr)->toBe('error message');
        });

        it('can create failure result with stdout', function () {
            $result = CommandResult::failure(
                stderr: 'error',
                exitCode: 2,
                executionTime: 0.1,
                stdout: 'partial output',
            );

            expect($result->stdout)->toBe('partial output')
                ->and($result->stderr)->toBe('error');
        });

        it('can create timeout result', function () {
            $result = CommandResult::timeout(
                timeoutSeconds: 30.0,
                command: 'long-running-command',
                partialOutput: 'partial',
            );

            expect($result->isTimedOut())->toBeTrue()
                ->and($result->exitCode)->toBe(124)
                ->and($result->stderr)->toContain('timed out')
                ->and($result->stdout)->toBe('partial');
        });
    });

    describe('status checks', function () {
        it('identifies successful commands', function () {
            $result = CommandResult::success('output', 0.1);

            expect($result->isSuccessful())->toBeTrue()
                ->and($result->isFailed())->toBeFalse()
                ->and($result->isTimedOut())->toBeFalse();
        });

        it('identifies failed commands', function () {
            $result = CommandResult::failure('error', 1, 0.1);

            expect($result->isSuccessful())->toBeFalse()
                ->and($result->isFailed())->toBeTrue()
                ->and($result->isTimedOut())->toBeFalse();
        });

        it('identifies timed out commands', function () {
            $result = CommandResult::timeout(30.0);

            expect($result->isSuccessful())->toBeFalse()
                ->and($result->isFailed())->toBeTrue()
                ->and($result->isTimedOut())->toBeTrue();
        });
    });

    describe('output handling', function () {
        it('returns stdout when no stderr', function () {
            $result = CommandResult::success('stdout only', 0.1);

            expect($result->output())->toBe('stdout only');
        });

        it('returns stderr when no stdout', function () {
            $result = CommandResult::failure('stderr only', 1, 0.1);

            expect($result->output())->toBe('stderr only');
        });

        it('combines stdout and stderr', function () {
            $result = new CommandResult(
                stdout: 'stdout content',
                stderr: 'stderr content',
                exitCode: 0,
                executionTime: 0.1,
            );

            expect($result->output())->toBe("stdout content\nstderr content");
        });

        it('checks for output presence', function () {
            $withOutput = CommandResult::success('output', 0.1);
            $withError = CommandResult::failure('error', 1, 0.1);
            $empty = new CommandResult('', '', 0, 0.1);

            expect($withOutput->hasOutput())->toBeTrue()
                ->and($withError->hasOutput())->toBeTrue()
                ->and($empty->hasOutput())->toBeFalse();
        });

        it('checks for error presence', function () {
            $withError = CommandResult::failure('error', 1, 0.1);
            $noError = CommandResult::success('output', 0.1);

            expect($withError->hasError())->toBeTrue()
                ->and($noError->hasError())->toBeFalse();
        });
    });

    describe('output lines', function () {
        it('splits output into lines', function () {
            $result = CommandResult::success("line1\nline2\nline3", 0.1);

            expect($result->outputLines())->toBe(['line1', 'line2', 'line3']);
        });

        it('returns empty array for empty output', function () {
            $result = new CommandResult('', '', 0, 0.1);

            expect($result->outputLines())->toBe([]);
        });

        it('splits stdout into lines', function () {
            $result = CommandResult::success("a\nb", 0.1);

            expect($result->stdoutLines())->toBe(['a', 'b']);
        });

        it('splits stderr into lines', function () {
            $result = CommandResult::failure("x\ny", 1, 0.1);

            expect($result->stderrLines())->toBe(['x', 'y']);
        });

        it('returns empty array for empty stdout', function () {
            $result = CommandResult::failure('error', 1, 0.1);

            expect($result->stdoutLines())->toBe([]);
        });

        it('returns empty array for empty stderr', function () {
            $result = CommandResult::success('output', 0.1);

            expect($result->stderrLines())->toBe([]);
        });
    });

    describe('formatted execution time', function () {
        it('formats sub-second times in milliseconds', function () {
            $result = CommandResult::success('output', 0.123);

            expect($result->formattedExecutionTime())->toBe('123ms');
        });

        it('formats times under 1ms', function () {
            $result = CommandResult::success('output', 0.0005);

            expect($result->formattedExecutionTime())->toBe('1ms');
        });

        it('formats times 1 second and above in seconds', function () {
            $result = CommandResult::success('output', 2.567);

            expect($result->formattedExecutionTime())->toBe('2.57s');
        });

        it('formats exactly 1 second', function () {
            $result = CommandResult::success('output', 1.0);

            expect($result->formattedExecutionTime())->toBe('1s');
        });
    });

    describe('toArray', function () {
        it('converts to array representation', function () {
            $result = CommandResult::success(
                stdout: 'output',
                executionTime: 0.5,
                command: 'ls -la',
            );

            $array = $result->toArray();

            expect($array)->toHaveKeys([
                'command',
                'stdout',
                'stderr',
                'exit_code',
                'execution_time',
                'executed_at',
                'is_successful',
            ])
                ->and($array['command'])->toBe('ls -la')
                ->and($array['stdout'])->toBe('output')
                ->and($array['exit_code'])->toBe(0)
                ->and($array['is_successful'])->toBeTrue();
        });
    });

    describe('immutability', function () {
        it('is a readonly class', function () {
            $result = CommandResult::success('output', 0.1);

            $reflection = new ReflectionClass($result);

            expect($reflection->isReadOnly())->toBeTrue();
        });
    });
});
