<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunMerinfoDataScriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public function __construct(public string $postNummer) {}

    public function handle(): void
    {
        $script = base_path('jobs/merinfo_data.mjs');
        $normalizedPostNummer = str_replace(' ', '', $this->postNummer);

        $process = new Process([
            'APP_URL='.config('app.url'),
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
                Log::error('RunMerinfoDataScriptJob stderr: '.trim($buffer));

                return;
            }

            Log::info('RunMerinfoDataScriptJob stdout: '.trim($buffer));
        });

        if ($exitCode !== 0 || ! $process->isSuccessful()) {
            Log::error('RunMerinfoDataScriptJob failed', [
                'post_nummer' => $this->postNummer,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getErrorOutput() ?: $process->getOutput(),
            ]);

            throw new \RuntimeException('merinfo_data.mjs failed with exit code '.$process->getExitCode());
        }

        Log::info('RunMerinfoDataScriptJob finished', ['post_nummer' => $this->postNummer]);
    }
}
