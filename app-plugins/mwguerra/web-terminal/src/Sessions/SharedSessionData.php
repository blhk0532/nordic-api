<?php

declare(strict_types=1);

namespace MWGuerra\WebTerminal\Sessions;

/**
 * Serializable session data for cross-worker persistence.
 *
 * This class holds session metadata that can be stored in Laravel's cache
 * to persist session information across PHP-FPM workers.
 */
class SharedSessionData
{
    /**
     * Create a new shared session data instance.
     *
     * @param  string  $sessionId  Unique session identifier
     * @param  string  $command  The command being executed
     * @param  int  $pid  Process ID (if available)
     * @param  int  $startedAt  Unix timestamp when session started
     * @param  int  $lastActivity  Unix timestamp of last activity
     * @param  int  $lastOutputPosition  Last read position in output capture
     * @param  string  $lastOutputHash  Hash of last captured output (for change detection)
     * @param  string  $backend  The backend used ('tmux', 'screen', 'memory')
     * @param  bool  $finished  Whether the process has finished
     * @param  int|null  $exitCode  Exit code if finished
     */
    public function __construct(
        public readonly string $sessionId,
        public readonly string $command,
        public int $pid = 0,
        public readonly int $startedAt = 0,
        public int $lastActivity = 0,
        public int $lastOutputPosition = 0,
        public string $lastOutputHash = '',
        public readonly string $backend = 'memory',
        public bool $finished = false,
        public ?int $exitCode = null,
    ) {
        if ($this->lastActivity === 0) {
            $this->lastActivity = $startedAt ?: time();
        }
    }

    /**
     * Convert to array for cache storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'command' => $this->command,
            'pid' => $this->pid,
            'started_at' => $this->startedAt,
            'last_activity' => $this->lastActivity,
            'last_output_position' => $this->lastOutputPosition,
            'last_output_hash' => $this->lastOutputHash,
            'backend' => $this->backend,
            'finished' => $this->finished,
            'exit_code' => $this->exitCode,
        ];
    }

    /**
     * Create from cached array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['session_id'],
            command: $data['command'],
            pid: $data['pid'] ?? 0,
            startedAt: $data['started_at'] ?? time(),
            lastActivity: $data['last_activity'] ?? time(),
            lastOutputPosition: $data['last_output_position'] ?? 0,
            lastOutputHash: $data['last_output_hash'] ?? '',
            backend: $data['backend'] ?? 'memory',
            finished: $data['finished'] ?? false,
            exitCode: $data['exit_code'] ?? null,
        );
    }
}
