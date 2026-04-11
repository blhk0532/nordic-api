<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class RunRatsitHittaScriptJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public function __construct(public string $postNummer) {}

    public function handle(): void
    {
        $script = base_path('jobs/ratsit_hitta.mjs');

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
                Log::error('RunRatsitHittaScriptJob stderr: '.trim($buffer));

                return;
            }

            Log::info('RunRatsitHittaScriptJob stdout: '.trim($buffer));
        });

        if ($exitCode !== 0 || ! $process->isSuccessful()) {
            Log::error('RunRatsitHittaScriptJob failed', [
                'post_nummer' => $this->postNummer,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getErrorOutput() ?: $process->getOutput(),
            ]);

            throw new RuntimeException('ratsit_hitta.mjs failed with exit code '.$process->getExitCode());
        }

        Log::info('RunRatsitHittaScriptJob finished', ['post_nummer' => $this->postNummer]);
    }
}
