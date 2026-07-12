<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\PaguTahunAnggaran;
use Illuminate\Support\Facades\Auth;

trait ScopesToUserStandar
{
    protected function isPrivilegedUser(): bool
    {
        $user = Auth::user();
        return $user->hasRole(['Super Admin', 'Pelaksana Komite', 'Kepala Madrasah']);
    }

    protected function allowedStandarIds(): ?array
    {
        if ($this->isPrivilegedUser()) {
            return null;
        }

        return Auth::user()->standars()->pluck('id')->toArray();
    }

    protected function activeTahunAnggaran(): ?PaguTahunAnggaran
    {
        // Prioritaskan tahun yang dipilih user via switcher (session)
        $selectedTahun = session('selected_tahun_anggaran');

        if ($selectedTahun) {
            $tahun = PaguTahunAnggaran::where('tahun', $selectedTahun)->first();

            if ($tahun) {
                return $tahun;
            }

            // Tahun tersimpan di session sudah tidak ada (mis. dihapus) — bersihkan
            // supaya request berikutnya tidak terus mencoba tahun yang invalid.
            session()->forget('selected_tahun_anggaran');
        }

        // Fall back ke tahun anggaran yang berstatus aktif
        return PaguTahunAnggaran::where('status_aktif', 1)->first();
    }

    protected function getSelectedTahun(): ?int
    {
        return session('selected_tahun_anggaran') ?? $this->activeTahunAnggaran()?->tahun;
    }
}
