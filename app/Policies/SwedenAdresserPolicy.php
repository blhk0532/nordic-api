<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SwedenAdresser;
use Illuminate\Auth\Access\HandlesAuthorization;

class SwedenAdresserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenAdresser');
    }

    public function view(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('View:SwedenAdresser');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenAdresser');
    }

    public function update(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('Update:SwedenAdresser');
    }

    public function delete(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('Delete:SwedenAdresser');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenAdresser');
    }

    public function restore(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('Restore:SwedenAdresser');
    }

    public function forceDelete(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('ForceDelete:SwedenAdresser');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenAdresser');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenAdresser');
    }

    public function replicate(AuthUser $authUser, SwedenAdresser $swedenAdresser): bool
    {
        return $authUser->can('Replicate:SwedenAdresser');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenAdresser');
    }

}