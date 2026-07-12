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
        // Check if user selected a specific tahun in session
        $selectedTahun = session('selected_tahun_anggaran');

        if ($selectedTahun) {
            return PaguTahunAnggaran::where('tahun', $selectedTahun)->first();
        }

        // Fall back to active tahun
        return PaguTahunAnggaran::where('status_aktif', 1)->first();
    }

    protected function getSelectedTahun(): ?int
    {
        return session('selected_tahun_anggaran') ?? $this->activeTahunAnggaran()?->tahun;
    }
}
