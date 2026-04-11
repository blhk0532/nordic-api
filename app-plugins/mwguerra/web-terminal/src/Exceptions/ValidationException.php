<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Exceptions;

use Exception;

/**
 * Exception thrown when command validation fails.
 *
 * This exception is thrown when a command fails validation,
 * such as when it's not in the whitelist or contains blocked characters.
 */
class ValidationException extends Exception
{
    /**
     * The command that failed validation.
     */
    public readonly ?string $command;

    /**
     * The validation error code.
     */
    public readonly string $errorCode;

    public function __construct(
        string $message,
        ?string $command = null,
        string $errorCode = 'validation_failed',
        int $code = 0,
        ?Exception $previous = null,
    ) {
        $this->command = $command;
        $this->errorCode = $errorCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an exception for a command not in the whitelist.
     */
    public static function notAllowed(string $command): self
    {
        // Extract just the binary name for the error message (don't reveal whitelist)
        $binary = self::extractBinary($command);

        return new self(
            message: "Command '{$binary}' is not allowed.",
            command: $command,
            errorCode: 'command_not_allowed',
        );
    }

    /**
     * Create an exception for blocked characters in input.
     */
    public static function blockedCharacters(string $command, string $character): self
    {
        return new self(
            message: 'Command contains invalid characters.',
            command: $command,
            errorCode: 'blocked_characters',
        );
    }

    /**
     * Create an exception for empty command.
     */
    public static function emptyCommand(): self
    {
        return new self(
            message: 'Command cannot be empty.',
            command: null,
            errorCode: 'empty_command',
        );
    }

    /**
     * Create an exception for command that is too long.
     */
    public static function tooLong(string $command, int $maxLength): self
    {
        return new self(
            message: "Command exceeds maximum length of {$maxLength} characters.",
            command: $command,
            errorCode: 'command_too_long',
        );
    }

    /**
     * Create an exception for potential injection attempt.
     */
    public static function injectionAttempt(string $command): self
    {
        return new self(
            message: 'Command contains potentially dangerous patterns.',
            command: $command,
            errorCode: 'injection_attempt',
        );
    }

    /**
     * Extract the binary name from a command string.
     */
    private static function extractBinary(string $command): string
    {
        $trimmed = trim($command);
        $parts = preg_split('/\s+/', $trimmed, 2);

        return $parts[0] ?? $trimmed;
    }

    /**
     * Check if this is a "not allowed" validation error.
     */
    public function isNotAllowed(): bool
    {
        return $this->errorCode === 'command_not_allowed';
    }

    /**
     * Check if this is a blocked characters error.
     */
    public function hasBlockedCharacters(): bool
    {
        return $this->errorCode === 'blocked_characters';
    }

    /**
     * Check if this is a potential injection attempt.
     */
    public function isInjectionAttempt(): bool
    {
        return $this->errorCode === 'injection_attempt';
    }

    /**
     * Get a user-friendly error message without revealing security details.
     */
    public function getUserMessage(): string
    {
        return match ($this->errorCode) {
            'command_not_allowed' => 'This command is not permitted.',
            'blocked_characters' => 'The command contains invalid characters.',
            'empty_command' => 'Please enter a command.',
            'command_too_long' => 'The command is too long.',
            'injection_attempt' => 'The command contains invalid input.',
            default => 'Command validation failed.',
        };
    }
}
