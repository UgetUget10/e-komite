<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProgramKegiatan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramKegiatanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProgramKegiatan');
    }

    public function view(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('View:ProgramKegiatan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProgramKegiatan');
    }

    public function update(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('Update:ProgramKegiatan');
    }

    public function delete(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('Delete:ProgramKegiatan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProgramKegiatan');
    }

    public function restore(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('Restore:ProgramKegiatan');
    }

    public function forceDelete(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('ForceDelete:ProgramKegiatan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProgramKegiatan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProgramKegiatan');
    }

    public function replicate(AuthUser $authUser, ProgramKegiatan $programKegiatan): bool
    {
        return $authUser->can('Replicate:ProgramKegiatan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProgramKegiatan');
    }

}