<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\DialerAttemptStatus;
use App\Enums\DialerLeadStatus;
use App\Models\DialerCallAttempt;
use App\Models\DialerLead;
use App\Services\AsteriskDialerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class OriginateAmiCallJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param  array<string, string>  $variables
     */
    public function __construct(
        public string $channel,
        public string $extension,
        public string $context = 'default',
        public int $priority = 1,
        public array $variables = [],
        public ?string $callerId = null,
        public int $timeoutMilliseconds = 30000,
        public ?int $attemptId = null,
        public ?string $actionId = null,
    ) {
        $this->onQueue('dialer');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1, 5, 15];
    }

    public function handle(AsteriskDialerService $dialerService): void
    {
        $didOriginate = $dialerService->originate(
            channel: $this->channel,
            extension: $this->extension,
            context: $this->context,
            priority: $this->priority,
            variables: $this->variables,
            callerId: $this->callerId,
            timeoutMilliseconds: $this->timeoutMilliseconds,
            actionId: $this->actionId,
        );

        if (! $didOriginate) {
            throw new RuntimeException('AMI originate command failed.');
        }

        if ($this->attemptId !== null) {
            DialerCallAttempt::query()
                ->whereKey($this->attemptId)
                ->update([
                    'status' => DialerAttemptStatus::Sent,
                    'sent_at' => now(),
                ]);

            DialerLead::query()
                ->whereKey(
                    DialerCallAttempt::query()
                        ->whereKey($this->attemptId)
                        ->value('dialer_lead_id'),
                )
                ->update([
                    'last_attempted_at' => now(),
                    'attempts_count' => DB::raw('attempts_count + 1'),
                ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('AMI originate job failed.', [
            'channel' => $this->channel,
            'extension' => $this->extension,
            'context' => $this->context,
            'exception' => $exception->getMessage(),
        ]);

        if ($this->attemptId !== null) {
            DialerCallAttempt::query()
                ->whereKey($this->attemptId)
                ->update([
                    'status' => DialerAttemptStatus::Failed,
                    'disposition' => 'originate_failed',
                    'ended_at' => now(),
                ]);

            DialerLead::query()
                ->whereKey(
                    DialerCallAttempt::query()
                        ->whereKey($this->attemptId)
                        ->value('dialer_lead_id'),
                )
                ->update([
                    'status' => DialerLeadStatus::Failed,
                    'last_disposition' => 'originate_failed',
                ]);
        }
    }
}
