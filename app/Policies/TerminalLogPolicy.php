<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use MWGuerra\WebTerminal\Models\TerminalLog;

class TerminalLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TerminalLog');
    }

    public function view(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('View:TerminalLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TerminalLog');
    }

    public function update(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('Update:TerminalLog');
    }

    public function delete(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('Delete:TerminalLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TerminalLog');
    }

    public function restore(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('Restore:TerminalLog');
    }

    public function forceDelete(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('ForceDelete:TerminalLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TerminalLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TerminalLog');
    }

    public function replicate(AuthUser $authUser, TerminalLog $terminalLog): bool
    {
        return $authUser->can('Replicate:TerminalLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TerminalLog');
    }
}
