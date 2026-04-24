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

class RunAdresserRatsitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200;

    public function __construct(public string $kommun)
    {
        $this->onQueue('ratsit');
    }

    public function handle(): void
    {
        $script = base_path('jobs/sweden_adresser_ratsit.mjs');

        $process = new Process([
            'node',
            $script,
            '--kommun='.$this->kommun,
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout(null);

        $exitCode = $process->run(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                Log::error('RunAdresserRatsitJob stderr: '.trim($buffer));

                return;
            }

            Log::info('RunAdresserRatsitJob stdout: '.trim($buffer));
        });

        if ($exitCode !== 0 || ! $process->isSuccessful()) {
            Log::error('RunAdresserRatsitJob failed', [
                'kommun' => $this->kommun,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getErrorOutput() ?: $process->getOutput(),
            ]);

            throw new \RuntimeException('sweden_adresser_ratsit.mjs failed with exit code '.$process->getExitCode());
        }

        Log::info('RunAdresserRatsitJob finished', ['kommun' => $this->kommun]);
    }
}
