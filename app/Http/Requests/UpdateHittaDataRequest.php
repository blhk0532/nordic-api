<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHittaDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnamn' => 'nullable|string|max:255',
            'alder' => 'nullable|string|max:10',
            'kon' => 'nullable|string|max:10',
            'gatuadress' => 'nullable|string|max:255',
            'postnummer' => 'nullable|string|max:10',
            'postort' => 'nullable|string|max:100',
            'telefon' => 'nullable',
            'telefonnummer' => 'nullable',
            'karta' => 'nullable|string|max:500',
            'link' => 'nullable|string|max:500',
            'bostadstyp' => 'nullable|string|max:100',
            'bostadspris' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_telefon' => 'nullable|boolean',
            'is_ratsit' => 'nullable|boolean',
            'is_hus' => 'nullable|boolean',
        ];
    }
}
