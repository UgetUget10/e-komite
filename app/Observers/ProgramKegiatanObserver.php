<?php

namespace App\Observers;

use App\Models\ProgramKegiatan;

class ProgramKegiatanObserver
{
    public function saving(ProgramKegiatan $programKegiatan): void
    {
        if ($programKegiatan->id_standar && $programKegiatan->kode) {
            $standar = $programKegiatan->standars;
            if ($standar) {
                $programKegiatan->kode_lengkap = ($standar->kode ?? '') . '.' . $programKegiatan->kode;
            }
        }
    }

    /**
     * Handle the ProgramKegiatan "created" event.
     */
    public function created(ProgramKegiatan $programKegiatan): void
    {
        //
    }

    /**
     * Handle the ProgramKegiatan "updated" event.
     */
    public function updated(ProgramKegiatan $programKegiatan): void
    {
        //
    }

    /**
     * Handle the ProgramKegiatan "deleted" event.
     */
    public function deleted(ProgramKegiatan $programKegiatan): void
    {
        //
    }

    /**
     * Handle the ProgramKegiatan "restored" event.
     */
    public function restored(ProgramKegiatan $programKegiatan): void
    {
        //
    }

    /**
     * Handle the ProgramKegiatan "force deleted" event.
     */
    public function forceDeleted(ProgramKegiatan $programKegiatan): void
    {
        //
    }
}
