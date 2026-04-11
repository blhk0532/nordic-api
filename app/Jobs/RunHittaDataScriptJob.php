<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunHittaDataScriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public function __construct(public string $postNummer) {}

    public function handle(): void
    {
        $script = base_path('jobs/hitta_data.mjs');
        $normalizedPostNummer = str_replace(' ', '', $this->postNummer);

        $process = new Process([
            'APP_URL='.config('app.url'), 'API_URL='.config('app.url'),
            'node',
            $script,
            $normalizedPostNummer,
            '--api-url',
            (string) config('app.url'),
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);

        $exitCode = $process->run(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                Log::error('RunHittaDataScriptJob stderr: '.trim($buffer));

                return;
            }

            Log::info('RunHittaDataScriptJob stdout: '.trim($buffer));
        });

        if ($exitCode !== 0 || ! $process->isSuccessful()) {
            Log::error('RunHittaDataScriptJob failed', [
                'post_nummer' => $this->postNummer,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getErrorOutput() ?: $process->getOutput(),
            ]);

            throw new \RuntimeException('hitta_data.mjs failed with exit code '.$process->getExitCode());
        }

        Log::info('RunHittaDataScriptJob finished', ['post_nummer' => $this->postNummer]);
    }
}
