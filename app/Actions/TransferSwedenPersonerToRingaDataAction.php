<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RingaData;
use App\Models\SwedenPersoner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class TransferSwedenPersonerToRingaDataAction
{
    /**
     * Transfer selected SwedenPersoner records to RingaData table.
     *
     * @param  Collection<SwedenPersoner>  $records
     * @param  array<string, mixed>  $data
     */
    public function handle(Collection $records, array $data): int
    {
        return DB::transaction(function () use ($records, $data): int {
            $createdCount = 0;

            $records->each(function (SwedenPersoner $record) use ($data, &$createdCount): void {
                if ($this->alreadyExistsInRingaData($record)) {
                    return;
                }

                RingaData::create([
                    'gatuadress' => $record->adress,
                    'postnummer' => $record->postnummer,
                    'postort' => $record->postort,
                    'forsamling' => $record->forsamling,
                    'kommun' => $record->kommun,
                    'kommun_ratsit' => $record->kommun,
                    'lan' => $record->lan,
                    'adressandring' => $record->adressandring,
                    'telfonnummer' => $record->telfonnummer,
                    'stjarntacken' => $record->stjarntacken,
                    'fodelsedag' => $record->fodelsedag,
                    'personnummer' => $record->personnummer,
                    'alder' => $record->alder,
                    'kon' => $record->kon,
                    'civilstand' => $record->civilstand,
                    'fornamn' => $record->fornamn,
                    'efternamn' => $record->efternamn,
                    'personnamn' => $record->personnamn,
                    'fodelsedag' => $record->ratsit_data['fodelsedag'] ?? null,
                    'telefon' => $record->telefon,
                    'telefonnummer' => $record->telefonnummer,
                    'epost_adress' => $record->epost_adress,
                    'bolagsengagemang' => $record->bolagsengagemang,
                    'agandeform' => $record->agandeform,
                    'bostadstyp' => $record->bostadstyp,
                    'boarea' => $record->boarea,
                    'byggar' => $record->byggar,
                    'fastighet' => $record->ratsit_data['fastighet'] ?? null,
                    'personer' => $record->ratsit_data['personer'] ?? null,
                    'foretag' => $record->ratsit_data['foretag'] ?? null,
                    'grannar' => $record->ratsit_data['grannar'] ?? null,
                    'fordon' => $record->ratsit_data['fordon'] ?? null,
                    'hundar' => $record->ratsit_data['hundar'] ?? null,
                    'longitude' => $record->ratsit_data['longitude'] ?? null,
                    'latitud' => $record->ratsit_data['latitud'] ?? null,
                    'google_maps' => $record->ratsit_data['google_maps'] ?? null,
                    'google_streetview' => $record->ratsit_data['google_streetview'] ?? null,
                    'ratsit_se' => $record->ratsit_se,
                    'is_active' => $record->is_active,
                    'is_telefon' => 1,
                    'is_hus' => 1,
                    'is_queued' => 1,
                    'user_id' => $data['user_id'] ?? Auth::id(),
                    'team_id' => $data['team_id'] ?? filament()->getTenant()?->id,
                    'status' => null,
                    'outcome' => null,
                    'attempts' => 0,
                ]);

                $createdCount++;
            });

            return $createdCount;
        });
    }

    private function alreadyExistsInRingaData(SwedenPersoner $record): bool
    {
        $query = RingaData::query();

        if (filled($record->personnummer)) {
            $query->where('personnummer', $record->personnummer);

            return $query->exists();
        }

        if (filled($record->telefon)) {
            $query
                ->where('telefon', $record->telefon)
                ->where('personnamn', $record->personnamn);

            return $query->exists();
        }

        $query
            ->where('personnamn', $record->personnamn)
            ->where('gatuadress', $record->adress)
            ->where('postnummer', $record->postnummer)
            ->where('postort', $record->postort);

        return $query->exists();
    }
}
