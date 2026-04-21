<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Person;
use App\Models\RingaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final readonly class TransferPeopleToRingaDataAction
{
    /**
     * Transfer selected Person records to RingaData table.
     *
     * @param  Collection<Person>  $records
     * @param  array<string, mixed>  $data
     */
    public function handle(Collection $records, array $data): void
    {
        DB::transaction(function () use ($records, $data): void {
            $records->each(function (Person $record) use ($data): void {
                RingaData::create([
                    'gatuadress' => $record->gatuadress,
                    'postnummer' => $record->postnummer,
                    'postort' => $record->postort,
                    'forsamling' => $record->forsamling ?? null,
                    'kommun' => $record->kommun ?? null,
                    'kommun_ratsit' => $record->kommun ?? null,
                    'lan' => $record->lan ?? null,
                    'adressandring' => $record->adressandring ?? null,
                    'telfonnummer' => $record->telefonnummer ?? null,
                    'stjarntacken' => null,
                    'fodelsedag' => $record->fodelsedag ?? null,
                    'personnummer' => $record->personnummer ?? null,
                    'alder' => $record->alder ?? null,
                    'kon' => $record->kon ?? null,
                    'civilstand' => $record->civilstand ?? null,
                    'fornamn' => $record->fornamn ?? null,
                    'efternamn' => $record->efternamn ?? null,
                    'personnamn' => $record->personnamn ?? null,
                    'telefon' => is_array($record->telefonnummer) ? ($record->telefonnummer[0] ?? null) : $record->telefonnummer,
                    'epost_adress' => $record->epost_adress ?? null,
                    'bolagsengagemang' => $record->bolagsengagemang ?? null,
                    'agandeform' => $record->agandeform ?? null,
                    'bostadstyp' => $record->bostadstyp ?? null,
                    'boarea' => $record->boarea ?? null,
                    'byggar' => $record->byggar ?? null,
                    'fastighet' => $record->fastighet ?? null,
                    'personer' => null,
                    'foretag' => null,
                    'grannar' => null,
                    'fordon' => null,
                    'hundar' => null,
                    'longitude' => $record->longitude ?? null,
                    'latitud' => $record->latitud ?? null,
                    'google_maps' => null,
                    'google_streetview' => null,
                    'ratsit_se' => null,
                    'is_active' => $record->is_active ?? true,
                    'is_telefon' => (bool) ($record->telefonnummer && count((array) $record->telefonnummer) > 0),
                    'is_hus' => $record->is_hus ?? false,
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
