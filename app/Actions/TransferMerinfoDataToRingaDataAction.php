<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\MerinfoData;
use App\Models\RingaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class TransferMerinfoDataToRingaDataAction
{
    /**
     * Transfer selected MerinfoData records to RingaData table.
     *
     * @param  Collection<MerinfoData>  $records
     * @param  array<string, mixed>  $data
     */
    public function handle(Collection $records, array $data): void
    {
        DB::transaction(function () use ($records, $data): void {
            $records->each(function (MerinfoData $record) use ($data): void {
                RingaData::create([
                    'gatuadress' => $record->gatuadress,
                    'postnummer' => $record->postnummer,
                    'postort' => $record->postort,
                    'kommun' => null,
                    'kommun_ratsit' => null,
                    'lan' => null,
                    'adressandring' => null,
                    'telfonnummer' => $record->telefoner ?? $record->telefonnummer ?? null,
                    'stjarntacken' => null,
                    'fodelsedag' => null,
                    'personnummer' => $record->personalNumber ?? null,
                    'alder' => $record->alder,
                    'kon' => $record->kon,
                    'civilstand' => null,
                    'fornamn' => $record->givenNameOrFirstName ?? null,
                    'efternamn' => null,
                    'personnamn' => $record->personnamn,
                    'telefon' => $record->telefon,
                    'epost_adress' => null,
                    'bolagsengagemang' => null,
                    'agandeform' => null,
                    'bostadstyp' => $record->bostadstyp,
                    'boarea' => null,
                    'byggar' => null,
                    'fastighet' => null,
                    'personer' => null,
                    'foretag' => null,
                    'grannar' => null,
                    'fordon' => null,
                    'hundar' => null,
                    'longitude' => null,
                    'latitud' => null,
                    'google_maps' => $record->karta ?? null,
                    'google_streetview' => null,
                    'ratsit_se' => $record->link ?? null,
                    'is_active' => $record->is_active,
                    'is_telefon' => $record->is_telefon,
                    'is_hus' => $record->is_hus,
                    'is_queued' => false,
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
