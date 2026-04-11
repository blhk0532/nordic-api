<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Exceptions;

use Exception;

/**
 * Exception thrown when rate limit is exceeded.
 *
 * This exception provides information about when the rate limit
 * will reset and how many attempts are allowed.
 */
class RateLimitException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $retryAfter = 0,
        public readonly int $maxAttempts = 1,
        int $code = 429,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an exception for too many requests.
     */
    public static function tooManyAttempts(int $retryAfter, int $maxAttempts = 1): self
    {
        return new self(
            message: "Too many commands. Please wait {$retryAfter} second(s) before trying again.",
            retryAfter: $retryAfter,
            maxAttempts: $maxAttempts,
        );
    }

    /**
     * Get the number of seconds until retry is allowed.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the maximum attempts allowed.
     */
    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * Get a user-friendly error message.
     */
    public function getUserMessage(): string
    {
        if ($this->retryAfter <= 1) {
            return 'Please wait a moment before sending another command.';
        }

        return "Please wait {$this->retryAfter} seconds before sending another command.";
    }

    /**
     * Get HTTP headers for rate limit response.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Retry-After' => (string) $this->retryAfter,
            'X-RateLimit-Limit' => (string) $this->maxAttempts,
            'X-RateLimit-Remaining' => '0',
        ];
    }
}
