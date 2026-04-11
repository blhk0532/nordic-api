<?php

declare(strict_types=1);

namespace App\Filament\Panels\App\Resources\Searches\Schemas;

use Filament\Schemas\Schema;

class SearchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
