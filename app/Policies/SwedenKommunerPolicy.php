<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SwedenKommuner;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SwedenKommunerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenKommuner');
    }

    public function view(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('View:SwedenKommuner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenKommuner');
    }

    public function update(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('Update:SwedenKommuner');
    }

    public function delete(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('Delete:SwedenKommuner');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenKommuner');
    }

    public function restore(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('Restore:SwedenKommuner');
    }

    public function forceDelete(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('ForceDelete:SwedenKommuner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenKommuner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenKommuner');
    }

    public function replicate(AuthUser $authUser, SwedenKommuner $swedenKommuner): bool
    {
        return $authUser->can('Replicate:SwedenKommuner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenKommuner');
    }
}
