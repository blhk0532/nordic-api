<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Attributes;

use Attribute;

/**
 * Validation attribute for host values.
 *
 * Validates that a host value is a valid hostname, IP address, or localhost.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class ValidHost
{
    public function __construct(
        public bool $allowIpv6 = true,
        public bool $allowLocalhost = true,
        public string $message = 'The host must be a valid hostname or IP address',
    ) {}

    /**
     * Validate the given host value.
     */
    public function validate(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        $lowerValue = strtolower($value);

        // Check for localhost
        if (in_array($lowerValue, ['localhost', '127.0.0.1', '::1'], true)) {
            return $this->allowLocalhost;
        }

        // Check for valid IPv4
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        // Check for valid IPv6
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->allowIpv6;
        }

        // Check for valid hostname
        return $this->isValidHostname($value);
    }

    /**
     * Check if a value is a valid hostname.
     */
    private function isValidHostname(string $value): bool
    {
        // Hostname pattern: RFC 1123
        $pattern = '/^(?=.{1,253}$)(?:(?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)*(?!-)[a-zA-Z0-9-]{1,63}(?<!-)$/';

        return preg_match($pattern, $value) === 1;
    }
}
