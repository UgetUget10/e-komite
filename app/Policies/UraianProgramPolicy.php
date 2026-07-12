<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UraianProgram;
use Illuminate\Auth\Access\HandlesAuthorization;

class UraianProgramPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UraianProgram');
    }

    public function view(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('View:UraianProgram');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UraianProgram');
    }

    public function update(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('Update:UraianProgram');
    }

    public function delete(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('Delete:UraianProgram');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:UraianProgram');
    }

    public function restore(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('Restore:UraianProgram');
    }

    public function forceDelete(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('ForceDelete:UraianProgram');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UraianProgram');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UraianProgram');
    }

    public function replicate(AuthUser $authUser, UraianProgram $uraianProgram): bool
    {
        return $authUser->can('Replicate:UraianProgram');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UraianProgram');
    }

}