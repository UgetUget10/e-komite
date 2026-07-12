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

    public static function updateProgram($programId)
    {
        if (!$programId) return;

        $program = ProgramKegiatan::find($programId);
        if (!$program) return;

        // Realisasi final (status = selesai) — dana yang sudah dipertanggungjawabkan penuh
        $totalSelesai = RealisasiNominal::where('fk_id_program_kegiatan', $programId)
            ->where('status', RealisasiNominal::STATUS_SELESAI)
            ->sum('nominal');

        // Pengajuan berjalan (status diajukan/berjalan) — dana yang sudah diajukan/dicairkan
        // tapi belum ada bukti/dokumen final
        $totalBerjalan = RealisasiNominal::where('fk_id_program_kegiatan', $programId)
            ->whereIn('status', RealisasiNominal::STATUSES_BERJALAN)
            ->sum('nominal');

        // Sisa = Pagu Komite - Realisasi Final - Pengajuan Berjalan + Pengembalian
        $sisa = $program->pagu_komite - $totalSelesai - $totalBerjalan + $program->pengembalian_sisa_anggaran;

        // Paksa update agar fillable tidak masalah
        $program->forceFill([
            'total_pengeluaran_perprogram' => $totalSelesai,
            'total_pengajuan_berjalan_perprogram' => $totalBerjalan,
            'sisa_anggaran_perprogram' => $sisa,
        ])->saveQuietly();
    }
}
