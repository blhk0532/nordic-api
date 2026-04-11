<?php

declare(strict_types=1);

use MWGuerra\WebTerminal\Security\SensitiveValue;

describe('SensitiveValue', function () {
    describe('construction', function () {
        it('stores the value internally', function () {
            $value = new SensitiveValue('secret123');

            expect($value->reveal())->toBe('secret123');
        });

        it('can be created from base64', function () {
            $encoded = base64_encode('secret123');
            $value = SensitiveValue::fromBase64($encoded);

            expect($value->reveal())->toBe('secret123');
        });

        it('throws on invalid base64', function () {
            expect(fn () => SensitiveValue::fromBase64('!!!invalid!!!'))->toThrow(
                InvalidArgumentException::class,
                'Invalid base64 encoding'
            );
        });

        it('can be created from environment variable', function () {
            putenv('TEST_SECRET=mysecret');

            $value = SensitiveValue::fromEnv('TEST_SECRET');

            expect($value->reveal())->toBe('mysecret');

            putenv('TEST_SECRET'); // Clean up
        });

        it('throws when environment variable not set', function () {
            expect(fn () => SensitiveValue::fromEnv('NONEXISTENT_VAR_12345'))->toThrow(
                InvalidArgumentException::class,
                'Environment variable not set'
            );
        });

        it('wraps string values', function () {
            $value = SensitiveValue::wrap('secret');

            expect($value)->toBeInstanceOf(SensitiveValue::class);
            expect($value->reveal())->toBe('secret');
        });

        it('returns same instance when wrapping SensitiveValue', function () {
            $original = new SensitiveValue('secret');
            $wrapped = SensitiveValue::wrap($original);

            expect($wrapped)->toBe($original);
        });
    });

    describe('reveal', function () {
        it('returns the underlying value', function () {
            $value = new SensitiveValue('my-secret-password');

            expect($value->reveal())->toBe('my-secret-password');
        });

        it('returns empty string for empty value', function () {
            $value = new SensitiveValue('');

            expect($value->reveal())->toBe('');
        });
    });

    describe('value inspection', function () {
        it('checks if value is empty', function () {
            $empty = new SensitiveValue('');
            $notEmpty = new SensitiveValue('secret');

            expect($empty->isEmpty())->toBeTrue();
            expect($empty->isNotEmpty())->toBeFalse();
            expect($notEmpty->isEmpty())->toBeFalse();
            expect($notEmpty->isNotEmpty())->toBeTrue();
        });

        it('returns the length of the value', function () {
            $value = new SensitiveValue('password123');

            expect($value->length())->toBe(11);
        });

        it('compares values securely', function () {
            $value = new SensitiveValue('secret');

            expect($value->equals('secret'))->toBeTrue();
            expect($value->equals('other'))->toBeFalse();
        });

        it('compares with another SensitiveValue', function () {
            $value1 = new SensitiveValue('secret');
            $value2 = new SensitiveValue('secret');
            $value3 = new SensitiveValue('different');

            expect($value1->equals($value2))->toBeTrue();
            expect($value1->equals($value3))->toBeFalse();
        });
    });

    describe('exposure prevention', function () {
        it('prevents exposure when cast to string', function () {
            $value = new SensitiveValue('super-secret');

            expect((string) $value)->toBe('[REDACTED]');
        });

        it('prevents exposure in JSON serialization', function () {
            $value = new SensitiveValue('api-key-12345');

            $json = json_encode(['credential' => $value]);

            expect($json)->toBe('{"credential":"[REDACTED]"}');
        });

        it('prevents exposure in debug info', function () {
            $value = new SensitiveValue('password');

            ob_start();
            var_dump($value);
            $output = ob_get_clean();

            expect($output)->toContain('[REDACTED]');
            expect($output)->not->toContain('password');
        });

        it('prevents serialization', function () {
            $value = new SensitiveValue('secret');

            expect(fn () => serialize($value))->toThrow(RuntimeException::class);
        });

        it('prevents cloning', function () {
            $value = new SensitiveValue('secret');

            expect(fn () => clone $value)->toThrow(RuntimeException::class);
        });
    });

    describe('encryption', function () {
        it('encrypts and decrypts value', function () {
            $value = new SensitiveValue('secret-data');

            $encrypted = $value->toEncrypted();

            expect($encrypted)->not->toBe('secret-data');
            expect($encrypted)->not->toContain('secret-data');

            $restored = SensitiveValue::fromEncrypted($encrypted);

            expect($restored->reveal())->toBe('secret-data');
        });

        it('produces different encrypted values each time', function () {
            $value = new SensitiveValue('same-data');

            $encrypted1 = $value->toEncrypted();
            $encrypted2 = $value->toEncrypted();

            // Laravel's encryption includes random IV
            expect($encrypted1)->not->toBe($encrypted2);

            // But both decrypt to same value
            expect(SensitiveValue::fromEncrypted($encrypted1)->reveal())
                ->toBe(SensitiveValue::fromEncrypted($encrypted2)->reveal());
        });
    });

    describe('file handling', function () {
        it('throws when file does not exist', function () {
            expect(fn () => SensitiveValue::fromFile('/nonexistent/file.key'))->toThrow(
                InvalidArgumentException::class,
                'File not found'
            );
        });
    });
});
