<?php

namespace App\Filament\Resources\SwedenPostnummers\Pages;

use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use Filament\Resources\Pages\Page;

class SwedenPostnummer extends Page
{
    protected static string $resource = SwedenPostnummerResource::class;

    protected string $view = 'filament.resources.sweden-postnummers.pages.sweden-postnummer';
}
