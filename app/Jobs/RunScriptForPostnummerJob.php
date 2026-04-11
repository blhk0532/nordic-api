<?php

declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

class RunScriptForPostnummerJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 9600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $scriptName,
        public string $postNummer
    ) {
        $this->onQueue('script');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->scriptName === '' || ! preg_match('/^[A-Za-z0-9._-]+$/', $this->scriptName)) {
            throw new Exception('Invalid script name.');
        }

        $scriptPath = base_path("scripts/{$this->scriptName}");

        if (! is_file($scriptPath)) {
            throw new Exception("Script not found: {$this->scriptName}");
        }

        $extension = strtolower((string) pathinfo($this->scriptName, PATHINFO_EXTENSION));

        $command = match ($extension) {
            'mjs', 'js' => ['node', $this->scriptName, $this->postNummer],
            'php' => ['php', $this->scriptName, $this->postNummer],
            'sh' => ['bash', $this->scriptName, $this->postNummer],
            'py' => ['python3', $this->scriptName, $this->postNummer],
            default => throw new Exception("Unsupported script type: .{$extension}"),
        };

        $result = Process::path(base_path('scripts'))
            ->timeout($this->timeout)
            ->run($command);

        if (! $result->successful()) {
            $error = trim($result->errorOutput());

            throw new Exception($error !== '' ? $error : 'Script execution failed.');
        }
    }
}
