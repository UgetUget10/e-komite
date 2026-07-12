<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaguTahunAnggaran;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaguTahunAnggaranPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaguTahunAnggaran');
    }

    public function view(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('View:PaguTahunAnggaran');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaguTahunAnggaran');
    }

    public function update(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('Update:PaguTahunAnggaran');
    }

    public function delete(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('Delete:PaguTahunAnggaran');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PaguTahunAnggaran');
    }

    public function restore(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('Restore:PaguTahunAnggaran');
    }

    public function forceDelete(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('ForceDelete:PaguTahunAnggaran');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaguTahunAnggaran');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaguTahunAnggaran');
    }

    public function replicate(AuthUser $authUser, PaguTahunAnggaran $paguTahunAnggaran): bool
    {
        return $authUser->can('Replicate:PaguTahunAnggaran');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaguTahunAnggaran');
    }

}