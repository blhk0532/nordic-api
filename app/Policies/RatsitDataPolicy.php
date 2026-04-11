<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RatsitData;
use Illuminate\Auth\Access\HandlesAuthorization;

class RatsitDataPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RatsitData');
    }

    public function view(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('View:RatsitData');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RatsitData');
    }

    public function update(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('Update:RatsitData');
    }

    public function delete(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('Delete:RatsitData');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RatsitData');
    }

    public function restore(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('Restore:RatsitData');
    }

    public function forceDelete(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('ForceDelete:RatsitData');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RatsitData');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RatsitData');
    }

    public function replicate(AuthUser $authUser, RatsitData $ratsitData): bool
    {
        return $authUser->can('Replicate:RatsitData');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RatsitData');
    }

}