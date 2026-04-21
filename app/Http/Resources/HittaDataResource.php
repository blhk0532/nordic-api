<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HittaDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'personnamn' => $this->personnamn,
            'alder' => $this->alder,
            'kon' => $this->kon,
            'gatuadress' => $this->gatuadress,
            'postnummer' => $this->postnummer,
            'postort' => $this->postort,
            'telefon' => $this->telefon,
            'telefonnumer' => $this->telefonnumer,
            'karta' => $this->karta,
            'link' => $this->link,
            'bostadstyp' => $this->bostadstyp,
            'bostadspris' => $this->bostadspris,
            'is_active' => $this->is_active,
            'is_telefon' => $this->is_telefon,
            'is_ratsit' => $this->is_ratsit,
            'is_hus' => $this->is_hus,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
