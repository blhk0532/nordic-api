<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HittaData;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HittaDataPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HittaData');
    }

    public function view(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('View:HittaData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HittaData');
    }

    public function update(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('Update:HittaData');
    }

    public function delete(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('Delete:HittaData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HittaData');
    }

    public function restore(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('Restore:HittaData');
    }

    public function forceDelete(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('ForceDelete:HittaData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HittaData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HittaData');
    }

    public function replicate(AuthUser $authUser, HittaData $hittaData): bool
    {
        return $authUser->can('Replicate:HittaData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HittaData');
    }
}
