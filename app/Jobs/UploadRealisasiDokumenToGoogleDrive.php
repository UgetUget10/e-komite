<?php

namespace App\Jobs;

use App\Models\RealisasiNominal;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadRealisasiDokumenToGoogleDrive implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $realisasiId)
    {
        //
    }

    public function handle(GoogleDriveService $googleDrive): void
    {
        $realisasi = RealisasiNominal::find($this->realisasiId);

        if (!$realisasi || blank($realisasi->dokumen)) {
            return;
        }

        try {
            // Nama folder tahun & bulan di Google Drive mengikuti tanggal realisasi yang
            // diinput user (bukan tahun anggaran yang sedang aktif) — supaya dokumen selalu
            // masuk ke folder tahun yang sesuai dengan tanggal kejadian realisasinya.
            if ($realisasi->tanggal_realisasi) {
                $tanggal = Carbon::parse($realisasi->tanggal_realisasi);
                $tahun = (string) $tanggal->year;
                $nomorBulan = $tanggal->month;
                $namaBulan = $tanggal->translatedFormat('F');
                $bulan = "$nomorBulan. $namaBulan";
            } else {
                $tahun = 'Tanpa Tahun';
                $bulan = 'Tanpa Bulan';
            }

            $rootFolder = 'realisasi RKAM';
            $folderPath = trim($rootFolder . '/' . $tahun . '/' . $bulan, '/');

            $localPath = Storage::disk('public')->path($realisasi->dokumen);
            $filename = basename($realisasi->dokumen);

            // Hapus file lama di Drive jika dokumen sedang diganti (edit)
            if (filled($realisasi->google_drive_path)) {
                $googleDrive->deleteFile($realisasi->google_drive_path);
            }

            $uploaded = $googleDrive->uploadFile($localPath, $filename, $folderPath);

            $realisasi->forceFill([
                'google_drive_file_id' => $uploaded['id'],
                'google_drive_url' => $uploaded['webViewLink'],
                'google_drive_path' => $uploaded['remotePath'],
            ])->saveQuietly();
        } catch (\Throwable $e) {
            Log::error('Gagal upload dokumen realisasi ke Google Drive (rclone)', [
                'realisasi_id' => $this->realisasiId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
