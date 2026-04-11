<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SwedenPersoner;
use Illuminate\Auth\Access\HandlesAuthorization;

class SwedenPersonerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenPersoner');
    }

    public function view(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('View:SwedenPersoner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenPersoner');
    }

    public function update(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('Update:SwedenPersoner');
    }

    public function delete(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('Delete:SwedenPersoner');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenPersoner');
    }

    public function restore(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('Restore:SwedenPersoner');
    }

    public function forceDelete(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('ForceDelete:SwedenPersoner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenPersoner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenPersoner');
    }

    public function replicate(AuthUser $authUser, SwedenPersoner $swedenPersoner): bool
    {
        return $authUser->can('Replicate:SwedenPersoner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenPersoner');
    }

}