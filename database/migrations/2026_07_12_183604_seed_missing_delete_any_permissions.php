<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Every Shield-generated Policy in this app was missing a deleteAny()
     * method, so Filament's DeleteBulkAction (which authorizes against
     * 'deleteAny', not just 'delete') silently failed for everyone,
     * including Super Admin — the "DeleteAny:{Resource}" permission never
     * existed in the database. See app/Policies/*.php for the matching
     * code fix (deleteAny() added to each Policy class).
     *
     * This seeds the missing permission per resource and grants it to
     * whichever role(s) already hold the corresponding "Delete:{Resource}"
     * permission, preserving existing intended access.
     */
    public function up(): void
    {
        $resources = [
            'RealisasiNominal',
            'PaguTahunAnggaran',
            'ProgramKegiatan',
            'RealisasiBulan',
            'Role',
            'StandarIsi',
            'Standar',
            'UploadBerkas',
            'UraianProgram',
            'User',
        ];

        foreach ($resources as $resource) {
            $deleteAnyName = "DeleteAny:{$resource}";
            $deleteName = "Delete:{$resource}";

            $permission = Permission::firstOrCreate([
                'name' => $deleteAnyName,
                'guard_name' => 'web',
            ]);

            $roles = Role::whereHas('permissions', fn ($q) => $q->where('name', $deleteName))->get();

            foreach ($roles as $role) {
                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $resources = [
            'RealisasiNominal',
            'PaguTahunAnggaran',
            'ProgramKegiatan',
            'RealisasiBulan',
            'Role',
            'StandarIsi',
            'Standar',
            'UploadBerkas',
            'UraianProgram',
            'User',
        ];

        foreach ($resources as $resource) {
            Permission::where('name', "DeleteAny:{$resource}")->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
