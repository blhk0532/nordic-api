<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunRatsitPersonsSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $search;

    public int $timeout = 3600;

    public function __construct(string $search)
    {
        $this->search = $search;
    }

    public function handle(): void
    {
        try {
            $inner = 'node '.escapeshellarg(base_path('jobs/ratsitPersons.mjs')).' '.escapeshellarg(str_replace(['/', '\\', '  '], ' ', $this->search));

            // Pass explicit API URL and token to the Node script so it doesn't rely
            // on possibly-missing environment vars when the job runs under the worker.
            $apiUrl = config('app.url') ?: env('APP_URL');
            $apiToken = env('LARAVEL_API_TOKEN') ?: env('API_TOKEN') ?: null;
            if ($apiUrl) {
                $inner .= ' --api-url '.escapeshellarg($apiUrl);
            }
            if ($apiToken) {
                $inner .= ' --api-token '.escapeshellarg($apiToken);
            }

            $command = 'bash -lc '.escapeshellarg($inner);

            $process = Process::fromShellCommandline($command);
            $process->setTimeout($this->timeout);

            Log::info('RunRatsitPersonsSearchJob starting', ['command' => $command]);

            // Start the process and stream incremental output to the logs so
            // we always have visibility into what the Node script is doing.
            $process->start();

            // Stream output while the process runs
            while ($process->isRunning()) {
                // Incremental stdout
                $chunkOut = $process->getIncrementalOutput();
                if ($chunkOut !== '') {
                    // Chunk long output to avoid extremely large single log entries
                    $pieces = str_split($chunkOut, 3000);
                    foreach ($pieces as $piece) {
                        Log::debug(['output' => $piece]);
                    }
                }

                // Incremental stderr
                $chunkErr = $process->getIncrementalErrorOutput();
                if ($chunkErr !== '') {
                    $pieces = str_split($chunkErr, 3000);
                    foreach ($pieces as $piece) {
                        Log::warning(['stderr' => $piece]);
                    }
                }

                usleep(100000); // 100ms
            }

            // Ensure remaining output is captured after exit
            $exit = $process->getExitCode();
            $out = $process->getOutput();
            $err = $process->getErrorOutput();

            Log::info('RunRatsitPersonsSearchJob finished', ['exit' => $exit]);

            if ($out) {
                $pieces = str_split($out, 4000);
                foreach ($pieces as $piece) {
                    Log::debug(['output' => $piece]);
                }
            }

            if ($err) {
                $pieces = str_split($err, 4000);
                foreach ($pieces as $piece) {
                    Log::warning('RunRatsitPersonsSearchJob stderr', ['stderr' => $piece]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('RunRatsitPersonsSearchJob failed', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}
