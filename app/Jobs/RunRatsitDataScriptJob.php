<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunRatsitDataScriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public function __construct(public string $postNummer) {}

    public function handle(): void
    {
        $script = base_path('jobs/ratsit_data.mjs');

        $process = new Process([
            'node',
            $script,
            $this->postNummer,
            '--api-url',
            (string) config('app.url'),
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);

        $exitCode = $process->run(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                Log::error('RunRatsitDataScriptJob stderr: '.trim($buffer));

                return;
            }

            Log::info('RunRatsitDataScriptJob stdout: '.trim($buffer));
        });

        if ($exitCode !== 0 || ! $process->isSuccessful()) {
            Log::error('RunRatsitDataScriptJob failed', [
                'post_nummer' => $this->postNummer,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getErrorOutput() ?: $process->getOutput(),
            ]);

            throw new \RuntimeException('ratsit_data.mjs failed with exit code '.$process->getExitCode());
        }

        Log::info('RunRatsitDataScriptJob finished', ['post_nummer' => $this->postNummer]);
    }
}
