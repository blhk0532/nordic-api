<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MerinfoData;
use Illuminate\Auth\Access\HandlesAuthorization;

class MerinfoDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MerinfoData');
    }

    public function view(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('View:MerinfoData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MerinfoData');
    }

    public function update(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('Update:MerinfoData');
    }

    public function delete(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('Delete:MerinfoData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MerinfoData');
    }

    public function restore(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('Restore:MerinfoData');
    }

    public function forceDelete(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('ForceDelete:MerinfoData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MerinfoData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MerinfoData');
    }

    public function replicate(AuthUser $authUser, MerinfoData $merinfoData): bool
    {
        return $authUser->can('Replicate:MerinfoData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MerinfoData');
    }

}