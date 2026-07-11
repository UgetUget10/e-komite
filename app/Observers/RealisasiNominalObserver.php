<?php

namespace App\Observers;

use App\Jobs\UploadRealisasiDokumenToGoogleDrive;
use App\Models\RealisasiNominal;
use App\Models\ProgramKegiatan;
use Illuminate\Support\Facades\Log;

class RealisasiNominalObserver
{
    public function created(RealisasiNominal $realisasi)
    {
        $this->updateProgram($realisasi->fk_id_program_kegiatan);

        if (filled($realisasi->dokumen)) {
            UploadRealisasiDokumenToGoogleDrive::dispatch($realisasi->id);
        }
    }

    public function updated(RealisasiNominal $realisasi)
    {
        $oldProgram = $realisasi->getOriginal('fk_id_program_kegiatan');

        if ($oldProgram && $oldProgram !== $realisasi->fk_id_program_kegiatan) {
            $this->updateProgram($oldProgram);
        }

        $this->updateProgram($realisasi->fk_id_program_kegiatan);

        if ($realisasi->wasChanged('dokumen') && filled($realisasi->dokumen)) {
            UploadRealisasiDokumenToGoogleDrive::dispatch($realisasi->id);
        }
    }

    public function deleted(RealisasiNominal $realisasi)
    {
        $this->updateProgram($realisasi->fk_id_program_kegiatan);
    }

    private function updateProgram($programId)
    {
        if (!$programId) return;

        $program = ProgramKegiatan::find($programId);
        if (!$program) return;

        // Hitung total nominal
        $total = RealisasiNominal::where('fk_id_program_kegiatan', $programId)->sum('nominal');

        // Hitung sisa anggaran
        $sisa = max(0, $program->pagu_komite - $total);

        // Paksa update agar fillable tidak masalah
        $program->forceFill([
            'total_pengeluaran_perprogram' => $total,
            'sisa_anggaran_perprogram' => $sisa,
        ])->save();
    }
}
