<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Merinfo;
use Illuminate\Auth\Access\HandlesAuthorization;

class MerinfoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Merinfo');
    }

    public function view(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('View:Merinfo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Merinfo');
    }

    public function update(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('Update:Merinfo');
    }

    public function delete(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('Delete:Merinfo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Merinfo');
    }

    public function restore(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('Restore:Merinfo');
    }

    public function forceDelete(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('ForceDelete:Merinfo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Merinfo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Merinfo');
    }

    public function replicate(AuthUser $authUser, Merinfo $merinfo): bool
    {
        return $authUser->can('Replicate:Merinfo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Merinfo');
    }

}