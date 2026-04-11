<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Contracts;

use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

interface HasCalendar
{
    public function getFormSchemaForModel(Schema $schema, ?string $model = null): Schema;

    public function refreshRecords(): static;

    public function getEventModel(): ?string;

    public function getEventRecord(): ?Model;
}
