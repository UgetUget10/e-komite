<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RealisasiNominal;
use Illuminate\Auth\Access\HandlesAuthorization;

class RealisasiNominalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RealisasiNominal');
    }

    public function view(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('View:RealisasiNominal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RealisasiNominal');
    }

    public function update(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('Update:RealisasiNominal');
    }

    public function delete(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('Delete:RealisasiNominal');
    }

    public function restore(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('Restore:RealisasiNominal');
    }

    public function forceDelete(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('ForceDelete:RealisasiNominal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RealisasiNominal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RealisasiNominal');
    }

    public function replicate(AuthUser $authUser, RealisasiNominal $realisasiNominal): bool
    {
        return $authUser->can('Replicate:RealisasiNominal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RealisasiNominal');
    }

}