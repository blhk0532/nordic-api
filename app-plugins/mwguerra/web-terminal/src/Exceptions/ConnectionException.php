<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Exceptions;

use Exception;
use MWGuerra\WebTerminal\Data\ConnectionConfig;
use MWGuerra\WebTerminal\Enums\ConnectionType;

/**
 * Exception thrown when connection-related errors occur.
 *
 * This exception covers connection establishment failures,
 * disconnection issues, and command execution problems.
 */
class ConnectionException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?ConnectionType $connectionType = null,
        public readonly ?string $host = null,
        int $code = 0,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create an exception for connection failure.
     */
    public static function connectionFailed(
        ConnectionConfig $config,
        string $reason = '',
        ?Exception $previous = null,
    ): self {
        $message = "Failed to establish {$config->type->label()} connection";

        if ($config->host !== null) {
            $message .= " to {$config->host}";
        }

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new self(
            message: $message,
            connectionType: $config->type,
            host: $config->host,
            previous: $previous,
        );
    }

    /**
     * Create an exception for not being connected.
     */
    public static function notConnected(): self
    {
        return new self(
            message: 'Not connected. Call connect() before executing commands.',
        );
    }

    /**
     * Create an exception for command execution failure.
     */
    public static function executionFailed(
        string $command,
        string $reason,
        ?ConnectionType $type = null,
        ?Exception $previous = null,
    ): self {
        $message = "Failed to execute command '{$command}'";

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new self(
            message: $message,
            connectionType: $type,
            previous: $previous,
        );
    }

    /**
     * Create an exception for authentication failure.
     */
    public static function authenticationFailed(
        ConnectionConfig $config,
        ?Exception $previous = null,
    ): self {
        $message = "Authentication failed for {$config->type->label()} connection";

        if ($config->host !== null) {
            $message .= " to {$config->host}";
        }

        if ($config->username !== null) {
            $message .= " as user '{$config->username}'";
        }

        return new self(
            message: $message,
            connectionType: $config->type,
            host: $config->host,
            previous: $previous,
        );
    }

    /**
     * Create an exception for timeout.
     */
    public static function timeout(
        string $command,
        float $timeoutSeconds,
        ?ConnectionType $type = null,
    ): self {
        return new self(
            message: "Command '{$command}' timed out after {$timeoutSeconds} seconds",
            connectionType: $type,
        );
    }

    /**
     * Create an exception for invalid configuration.
     */
    public static function invalidConfig(
        string $reason,
        ?ConnectionType $type = null,
    ): self {
        return new self(
            message: "Invalid connection configuration: {$reason}",
            connectionType: $type,
        );
    }

    /**
     * Create an exception for disconnection failure.
     */
    public static function disconnectionFailed(
        string $reason = '',
        ?ConnectionType $type = null,
        ?Exception $previous = null,
    ): self {
        $message = 'Failed to disconnect';

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new self(
            message: $message,
            connectionType: $type,
            previous: $previous,
        );
    }

    /**
     * Get a user-friendly error message.
     *
     * This returns a sanitized message suitable for display to end users,
     * without exposing sensitive internal details.
     */
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
