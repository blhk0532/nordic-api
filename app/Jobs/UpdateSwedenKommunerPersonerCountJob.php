<?php

namespace App\Jobs;

use App\Models\SwedenPersoner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class UpdateSwedenKommunerPersonerCountJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(public Collection $records)
    {
        $this->onQueue('sweden-postnummer');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->records as $record) {
            if (empty($record->kommun)) {
                continue;
            }

            $count = SwedenPersoner::where('kommun', $record->kommun)->count();
            $record->update(['personer_count' => $count]);
        }
    }
}
