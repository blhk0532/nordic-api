<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SwedenPersonerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fornamn' => $this->fornamn,
            'efternamn' => $this->efternamn,
            'personnamn' => $this->personnamn,
            'adress' => $this->adress,
            'postnummer' => $this->postnummer,
            'postort' => $this->postort,
            'kommun' => $this->kommun,
            'lan' => $this->lan,
            'telefon' => $this->telefon,
            'kon' => $this->kon,
            'alder' => $this->alder,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
