<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SwedenPostorter;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SwedenPostorterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenPostorter');
    }

    public function view(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('View:SwedenPostorter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenPostorter');
    }

    public function update(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('Update:SwedenPostorter');
    }

    public function delete(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('Delete:SwedenPostorter');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenPostorter');
    }

    public function restore(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('Restore:SwedenPostorter');
    }

    public function forceDelete(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('ForceDelete:SwedenPostorter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenPostorter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenPostorter');
    }

    public function replicate(AuthUser $authUser, SwedenPostorter $swedenPostorter): bool
    {
        return $authUser->can('Replicate:SwedenPostorter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenPostorter');
    }
}
