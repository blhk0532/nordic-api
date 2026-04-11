<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Enums\OutputType;

describe('OutputType', function () {
    it('has all expected cases', function () {
        expect(OutputType::cases())->toHaveCount(6);
        expect(OutputType::Stdout->value)->toBe('stdout');
        expect(OutputType::Stderr->value)->toBe('stderr');
        expect(OutputType::Error->value)->toBe('error');
        expect(OutputType::Info->value)->toBe('info');
        expect(OutputType::Command->value)->toBe('command');
        expect(OutputType::System->value)->toBe('system');
    });

    it('returns correct labels', function () {
        expect(OutputType::Stdout->label())->toBe('Standard Output');
        expect(OutputType::Stderr->label())->toBe('Standard Error');
        expect(OutputType::Error->label())->toBe('Error');
        expect(OutputType::Info->label())->toBe('Info');
        expect(OutputType::Command->label())->toBe('Command');
        expect(OutputType::System->label())->toBe('System');
    });

    it('returns correct CSS classes', function () {
        expect(OutputType::Stdout->cssClass())->toBe('terminal-stdout');
        expect(OutputType::Stderr->cssClass())->toBe('terminal-stderr');
        expect(OutputType::Error->cssClass())->toBe('terminal-error');
        expect(OutputType::Info->cssClass())->toBe('terminal-info');
        expect(OutputType::Command->cssClass())->toBe('terminal-command');
        expect(OutputType::System->cssClass())->toBe('terminal-system');
    });

    it('returns correct colors', function () {
        expect(OutputType::Stdout->color())->toBe('#00ff00');
        expect(OutputType::Stderr->color())->toBe('#ff6b6b');
        expect(OutputType::Error->color())->toBe('#ff0000');
    });

    it('correctly identifies error types', function () {
        expect(OutputType::Stderr->isErrorType())->toBeTrue();
        expect(OutputType::Error->isErrorType())->toBeTrue();
        expect(OutputType::Stdout->isErrorType())->toBeFalse();
        expect(OutputType::Info->isErrorType())->toBeFalse();
        expect(OutputType::Command->isErrorType())->toBeFalse();
        expect(OutputType::System->isErrorType())->toBeFalse();
    });
});
