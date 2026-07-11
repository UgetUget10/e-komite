<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UploadBerkas;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadBerkasPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UploadBerkas');
    }

    public function view(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('View:UploadBerkas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UploadBerkas');
    }

    public function update(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('Update:UploadBerkas');
    }

    public function delete(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('Delete:UploadBerkas');
    }

    public function restore(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('Restore:UploadBerkas');
    }

    public function forceDelete(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('ForceDelete:UploadBerkas');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UploadBerkas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UploadBerkas');
    }

    public function replicate(AuthUser $authUser, UploadBerkas $uploadBerkas): bool
    {
        return $authUser->can('Replicate:UploadBerkas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UploadBerkas');
    }

}