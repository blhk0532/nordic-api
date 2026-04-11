<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Membership;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        $teamId = $user->current_team_id ?? filament()->getTenant()?->id ?? auth()->user()?->current_team_id;

        if (! $teamId) {
            return;
        }

        Membership::firstOrCreate([
            'team_id' => $teamId,
            'user_id' => $user->id,
        ]);
    }
}
