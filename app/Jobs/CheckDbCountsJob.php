<?php

namespace App\Jobs;

use App\Models\HittaData;
use App\Models\Person;
use App\Models\SwedenPersoner;
use App\Models\SwedenPostnummer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class CheckDbCountsJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

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
            $postNummer = (string) $record->postnummer;

            $hittaCount = HittaData::query()
                ->where('postnummer', $postNummer)
                ->count();

            $merinfoCount = Person::query()
                ->where('zip', $postNummer)
                ->count();

            $ratsitCount = SwedenPersoner::query()
                ->where('postnummer', $postNummer)
                ->count();

            SwedenPostnummer::query()
                ->whereKey($record->getKey())
                ->update([
                    'personer_hitta_saved' => $hittaCount,
                    'personer_merinfo_saved' => $merinfoCount,
                    'personer_ratsit_saved' => $ratsitCount,
                ]);
        }
    }
}
