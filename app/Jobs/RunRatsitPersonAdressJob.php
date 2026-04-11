<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RatsitAdress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RunRatsitPersonAdressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Default queue for this job.
     */
    /**
     * Maximum number of seconds the job may run (1 hour).
     */
    public int $timeout = 3600;

    public function __construct(public ?int $ratsitAdressId = null, public ?string $url = null) {}

    public function handle(): void
    {
        // If a URL was passed explicitly (from the bulk action), use it directly.
        if (! empty($this->url)) {
            $url = $this->url;
            Log::debug('Using provided URL for Ratsit job', ['id' => $this->ratsitAdressId, 'url' => $url]);
        } else {
            $record = null;
            if ($this->ratsitAdressId !== null) {
                $record = RatsitAdress::find($this->ratsitAdressId);
            }

            if (! $record) {
                Log::error('RatsitAdress record not found and no URL provided', ['id' => $this->ratsitAdressId]);

                return;
            }

            $postOrt = $record->post_ort;
            $postNummer = preg_replace('/\s+/', '', (string) $record->post_nummer);
            $postOrtSlug = str_replace(' ', '-', $postOrt);

            // Prefer an explicit saved `personer_link` if available (it may include municipality
            // and correct path format). Fall back to constructing a URL from `post_ort` and
            // `post_nummer` when no saved link exists.
            $url = null;

            if (! empty($record->personer_link)) {
                $link = (string) $record->personer_link;
                // If relative path, prefix with domain
                if (str_starts_with($link, '/')) {
                    $url = 'https://www.ratsit.se'.$link;
                } elseif (filter_var($link, FILTER_VALIDATE_URL)) {
                    $url = $link;
                }

                if ($url) {
                    Log::debug('Using stored personer_link for Ratsit URL', ['id' => $this->ratsitAdressId, 'url' => $url]);
                }
            }

            if (! $url) {
                $url = "https://www.ratsit.se/personer/{$postOrtSlug}-{$postNummer}";
                Log::debug('Using constructed Ratsit URL', ['id' => $this->ratsitAdressId, 'url' => $url]);
            }
        }

        $projectRoot = base_path();
        $script = $projectRoot.'/jobs/ratsit_person_adresser.mjs';

        // Build a shell command that mirrors running it in the terminal
        $command = sprintf('node %s %s', escapeshellarg($script), escapeshellarg($url));
        // Use bash -lc so the command runs through the shell (like a terminal)
        $process = new Process(['bash', '-lc', $command], $projectRoot);
        $process->setTimeout(36000);

        // Diagnostic: log environment and node availability to help debug
        try {
            $path = getenv('PATH') ?: 'n/a';
            $user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] ?? get_current_user() : get_current_user();
            Log::debug('Running Ratsit scraper job', ['id' => $this->ratsitAdressId, 'user' => $user, 'cwd' => $projectRoot, 'PATH' => $path]);

            // Probe node availability
            try {
                $probe = new Process(['node', '--version']);
                $probe->run();
                $nodeVersion = trim($probe->getOutput());
            } catch (\Throwable $probeEx) {
                $nodeVersion = 'node probe failed: '.$probeEx->getMessage();
            }

            Log::debug('Node probe', ['version' => $nodeVersion]);

            // Run the scraper process
            $process->run();

            $exit = $process->getExitCode();
            $stdout = $process->getOutput();
            $stderr = $process->getErrorOutput();

            Log::debug('Ratsit scraper process finished', ['exit' => $exit, 'stdout_snippet' => substr($stdout, 0, 2000), 'stderr_snippet' => substr($stderr, 0, 2000)]);

            if ($process->isSuccessful()) {
                Log::info('Ratsit scraper completed for id '.$this->ratsitAdressId.': '.$stdout);
            } else {
                Log::error('Ratsit scraper failed for id '.$this->ratsitAdressId.': '.$stderr);
            }
        } catch (\Throwable $e) {
            Log::error('Ratsit scraper exception for id '.$this->ratsitAdressId.': '.$e->getMessage());
        }
    }
}
