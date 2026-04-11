<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Attributes;

use Attribute;

/**
 * Validation attribute for file/directory path values.
 *
 * Validates that a path value is syntactically valid and optionally
 * checks for dangerous patterns like path traversal.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class ValidPath
{
    public function __construct(
        public bool $allowRelative = false,
        public bool $blockTraversal = true,
        public string $message = 'The path must be a valid file system path',
    ) {}

    /**
     * Validate the given path value.
     */
    public function validate(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        // Block path traversal attempts
        if ($this->blockTraversal && $this->containsTraversal($value)) {
            return false;
        }

        // Check for absolute path if required
        if (! $this->allowRelative && ! $this->isAbsolute($value)) {
            return false;
        }

        // Check for valid path characters
        return $this->hasValidCharacters($value);
    }

    /**
     * Check if the path contains traversal patterns.
     */
    private function containsTraversal(string $path): bool
    {
        // Normalize separators
        $normalized = str_replace('\\', '/', $path);

        // Check for various traversal patterns
        $patterns = [
            '/../',
            '/..',
            '../',
            '..\\',
            '/..\\',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        // Check if path starts with ..
        if (str_starts_with($normalized, '..')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the path is absolute.
     */
    private function isAbsolute(string $path): bool
    {
        // Unix absolute path
        if (str_starts_with($path, '/')) {
            return true;
        }

        // Windows absolute path (C:\, D:\, etc.)
        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $path)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the path contains only valid characters.
     */
    private function hasValidCharacters(string $path): bool
    {
        // Block null bytes
        if (str_contains($path, "\0")) {
            return false;
        }

        // Block control characters
        if (preg_match('/[\x00-\x1f\x7f]/', $path)) {
            return false;
        }

        return true;
    }
}
