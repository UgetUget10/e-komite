<?php

namespace App\Observers;

use App\Models\ProgramKegiatan;

class ProgramKegiatanObserver
{
    public function saving(ProgramKegiatan $programKegiatan): void
    {
        if ($programKegiatan->id_standar && $programKegiatan->kode) {
            $standar = $programKegiatan->standar;
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
        // Jika pengembalian sisa anggaran diubah manual (bukan lewat observer realisasi),
        // hitung ulang sisa_anggaran_perprogram supaya tetap konsisten dengan formula:
        // Sisa = Pagu Komite - Realisasi Final - Pengajuan Berjalan + Pengembalian
        if ($programKegiatan->wasChanged('pengembalian_sisa_anggaran') && ! $programKegiatan->wasRecentlyCreated) {
            \App\Observers\RealisasiNominalObserver::updateProgram($programKegiatan->id);
        }
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
