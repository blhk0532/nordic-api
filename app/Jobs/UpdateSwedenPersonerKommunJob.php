<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\UpdateSwedenPersonerAction;
use App\Models\SwedenPersoner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class UpdateSwedenPersonerKommunJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  Collection<int, int>  $recordIds
     */
    public function __construct(public Collection $recordIds)
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        SwedenPersoner::query()
            ->whereIn('id', $this->recordIds)
            ->chunkById(100, function ($records) {
                foreach ($records as $record) {
                    UpdateSwedenPersonerAction::execute($record, false);
                }
            });
    }
}
