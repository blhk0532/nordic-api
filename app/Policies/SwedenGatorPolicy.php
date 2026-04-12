<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SwedenGator;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SwedenGatorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenGator');
    }

    public function view(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('View:SwedenGator');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenGator');
    }

    public function update(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('Update:SwedenGator');
    }

    public function delete(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('Delete:SwedenGator');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenGator');
    }

    public function restore(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('Restore:SwedenGator');
    }

    public function forceDelete(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('ForceDelete:SwedenGator');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenGator');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenGator');
    }

    public function replicate(AuthUser $authUser, SwedenGator $swedenGator): bool
    {
        return $authUser->can('Replicate:SwedenGator');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenGator');
    }
}
