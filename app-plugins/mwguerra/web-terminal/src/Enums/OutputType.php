<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Enums;

/**
 * Enum representing different types of terminal output for styling purposes.
 */
enum OutputType: string
{
    case Stdout = 'stdout';
    case Stderr = 'stderr';
    case Error = 'error';
    case Info = 'info';
    case Command = 'command';
    case System = 'system';

    /**
     * Get a human-readable label for the output type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Stdout => 'Standard Output',
            self::Stderr => 'Standard Error',
            self::Error => 'Error',
            self::Info => 'Info',
            self::Command => 'Command',
            self::System => 'System',
        };
    }

    /**
     * Get the CSS class for styling this output type in the terminal.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Stdout => 'terminal-stdout',
            self::Stderr => 'terminal-stderr',
            self::Error => 'terminal-error',
            self::Info => 'terminal-info',
            self::Command => 'terminal-command',
            self::System => 'terminal-system',
        };
    }

    /**
     * Get the text color for this output type.
     */
    public function color(): string
    {
        return match ($this) {
            self::Stdout => '#00ff00',
            self::Stderr => '#ff6b6b',
            self::Error => '#ff0000',
            self::Info => '#00bfff',
            self::Command => '#ffffff',
            self::System => '#888888',
        };
    }

    /**
     * Check if this output type represents an error condition.
     */
    public function isErrorType(): bool
    {
        return match ($this) {
            self::Stderr, self::Error => true,
            default => false,
        };
    }
}
