<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Sessions;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

/**
 * Represents an interactive process session.
 *
 * Holds the running process, its input stream, and timing metadata.
 */
class ProcessSession
{
    /**
     * Create a new process session.
     *
     * @param  Process  $process  The running Symfony Process
     * @param  InputStream  $inputStream  The input stream for writing to stdin
     * @param  int  $startedAt  Unix timestamp when session started
     * @param  int  $lastActivity  Unix timestamp of last activity (read/write)
     */
    public function __construct(
        public readonly Process $process,
        public readonly InputStream $inputStream,
        public readonly int $startedAt,
        public int $lastActivity = 0,
    ) {
        if ($this->lastActivity === 0) {
            $this->lastActivity = $startedAt;
        }
    }
}
