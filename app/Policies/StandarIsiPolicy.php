<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StandarIsi;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandarIsiPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StandarIsi');
    }

    public function view(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('View:StandarIsi');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StandarIsi');
    }

    public function update(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('Update:StandarIsi');
    }

    public function delete(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('Delete:StandarIsi');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StandarIsi');
    }

    public function restore(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('Restore:StandarIsi');
    }

    public function forceDelete(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('ForceDelete:StandarIsi');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StandarIsi');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StandarIsi');
    }

    public function replicate(AuthUser $authUser, StandarIsi $standarIsi): bool
    {
        return $authUser->can('Replicate:StandarIsi');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StandarIsi');
    }

}