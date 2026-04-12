<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SwedenPostnummer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SwedenPostnummerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SwedenPostnummer');
    }

    public function view(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('View:SwedenPostnummer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SwedenPostnummer');
    }

    public function update(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('Update:SwedenPostnummer');
    }

    public function delete(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('Delete:SwedenPostnummer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SwedenPostnummer');
    }

    public function restore(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('Restore:SwedenPostnummer');
    }

    public function forceDelete(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('ForceDelete:SwedenPostnummer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SwedenPostnummer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SwedenPostnummer');
    }

    public function replicate(AuthUser $authUser, SwedenPostnummer $swedenPostnummer): bool
    {
        return $authUser->can('Replicate:SwedenPostnummer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SwedenPostnummer');
    }
}
