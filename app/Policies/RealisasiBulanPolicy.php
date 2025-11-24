<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RealisasiBulan;
use Illuminate\Auth\Access\HandlesAuthorization;

class RealisasiBulanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RealisasiBulan');
    }

    public function view(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('View:RealisasiBulan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RealisasiBulan');
    }

    public function update(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('Update:RealisasiBulan');
    }

    public function delete(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('Delete:RealisasiBulan');
    }

    public function restore(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('Restore:RealisasiBulan');
    }

    public function forceDelete(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('ForceDelete:RealisasiBulan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RealisasiBulan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RealisasiBulan');
    }

    public function replicate(AuthUser $authUser, RealisasiBulan $realisasiBulan): bool
    {
        return $authUser->can('Replicate:RealisasiBulan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RealisasiBulan');
    }

}