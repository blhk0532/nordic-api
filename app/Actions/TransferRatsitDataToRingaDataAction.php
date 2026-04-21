<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\RatsitData;
use App\Models\RingaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class TransferRatsitDataToRingaDataAction
{
    /**
     * Transfer selected RatsitData records to RingaData table.
     *
     * @param  Collection<RatsitData>  $records
     * @param  array<string, mixed>  $data
     */
    public function handle(Collection $records, array $data): void
    {
        DB::transaction(function () use ($records, $data): void {
            $records->each(function (RatsitData $record) use ($data): void {
                RingaData::create([
                    'gatuadress' => $record->gatuadress,
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
                    'telefon' => $record->telefon,
                    'epost_adress' => $record->epost_adress,
                    'bolagsengagemang' => $record->bolagsengagemang,
                    'agandeform' => $record->agandeform,
                    'bostadstyp' => $record->bostadstyp,
                    'boarea' => $record->boarea,
                    'byggar' => $record->byggar,
                    'fastighet' => $record->fastighet,
                    'personer' => $record->personer,
                    'foretag' => $record->foretag,
                    'grannar' => $record->grannar,
                    'fordon' => $record->fordon,
                    'hundar' => $record->hundar,
                    'longitude' => $record->longitude,
                    'latitud' => $record->latitud,
                    'google_maps' => $record->google_maps,
                    'google_streetview' => $record->google_streetview,
                    'ratsit_se' => $record->ratsit_se,
                    'is_active' => $record->is_active,
                    'is_telefon' => $record->is_telefon,
                    'is_hus' => $record->is_hus,
                    'is_queued' => $record->is_queued,
                    'user_id' => $data['user_id'] ?? auth()->id(),
                    'team_id' => $data['team_id'] ?? filament()->getTenant()?->id,
                    'status' => null,
                    'outcome' => null,
                    'attempts' => 0,
                ]);
            });
        });
    }
}
