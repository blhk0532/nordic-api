<?php

declare(strict_types=1);

namespace App\Filament\Resources\JobBatches\Pages;

use App\Filament\Resources\JobBatches\JobBatchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobBatch extends CreateRecord
{
    protected static string $resource = JobBatchResource::class;
}
