<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Security;

use MWGuerra\WebTerminal\Exceptions\ValidationException;

/**
 * Result of a command validation check.
 *
 * This value object encapsulates the result of validating a command,
 * providing a clear interface for checking validity and accessing errors.
 */
readonly class ValidationResult
{
    /**
     * Create a new ValidationResult instance.
     */
    public function __construct(
        public bool $valid,
        public ?string $command = null,
        public ?ValidationException $exception = null,
    ) {}

    /**
     * Create a passed validation result.
     */
    public static function passed(string $command): self
    {
        return new self(
            valid: true,
            command: $command,
        );
    }

    /**
     * Create a failed validation result.
     */
    public static function failed(ValidationException $exception): self
    {
        return new self(
            valid: false,
            command: $exception->command,
            exception: $exception,
        );
    }

    /**
     * Check if the validation passed.
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Check if the validation failed.
     */
    public function isFailed(): bool
    {
        return ! $this->valid;
    }

    /**
     * Get the validation exception if failed.
     */
    public function getException(): ?ValidationException
    {
        return $this->exception;
    }

    /**
     * Get the error message if failed.
     */
    public function getErrorMessage(): ?string
    {
        return $this->exception?->getMessage();
    }

    /**
     * Get a user-friendly error message if failed.
     */
    public function getUserMessage(): ?string
    {
        return $this->exception?->getUserMessage();
    }

    /**
     * Get the error code if failed.
     */
    public function getErrorCode(): ?string
    {
        return $this->exception?->errorCode;
    }
}
