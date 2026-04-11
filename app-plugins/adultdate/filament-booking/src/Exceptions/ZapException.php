<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Exceptions;

use Exception;

abstract class ZapException extends Exception
{
    /**
     * The error context data.
     */
    protected array $context = [];

    /**
     * Set context data for the exception.
     */
    final public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the error context.
     */
    final public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the exception as an array.
     */
    final public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->getContext(),
        ];
    }
}
