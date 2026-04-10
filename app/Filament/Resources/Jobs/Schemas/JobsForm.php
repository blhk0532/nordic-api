<?php

declare(strict_types=1);

namespace App\Filament\Resources\Jobs\Schemas;

use Filament\Schemas\Schema;

class JobsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
