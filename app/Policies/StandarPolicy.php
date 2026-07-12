<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Standar;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandarPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Standar');
    }

    public function view(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('View:Standar');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Standar');
    }

    public function update(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('Update:Standar');
    }

    public function delete(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('Delete:Standar');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Standar');
    }

    public function restore(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('Restore:Standar');
    }

    public function forceDelete(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('ForceDelete:Standar');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Standar');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Standar');
    }

    public function replicate(AuthUser $authUser, Standar $standar): bool
    {
        return $authUser->can('Replicate:Standar');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Standar');
    }

}