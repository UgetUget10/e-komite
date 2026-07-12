<?php

namespace App\Livewire;

use App\Models\PaguTahunAnggaran;
use Livewire\Component;

class TahunAnggaranSwitcher extends Component
{
    public $selectedTahun = null;
    public $tahunList = [];
    public $isActiveTahun = true;

    public function mount()
    {
        $this->loadTahunList();
        $activeTahun = $this->getActiveTahun();
        $this->selectedTahun = session('selected_tahun_anggaran') ?? $activeTahun;
        $this->isActiveTahun = $this->selectedTahun == $activeTahun;
    }

    public function loadTahunList()
    {
        $this->tahunList = PaguTahunAnggaran::orderByDesc('tahun')
            ->pluck('tahun', 'id')
            ->toArray();
    }

    public function getActiveTahun()
    {
        $active = PaguTahunAnggaran::where('status_aktif', 1)->first();
        return $active?->tahun ?? now()->year;
    }

    public function switchTahun($tahun)
    {
        session(['selected_tahun_anggaran' => $tahun]);
        $this->selectedTahun = $tahun;

        // Broadcast event untuk update semua components
        $this->dispatch('tahun-changed', tahun: $tahun);

        // Redirect ke dashboard untuk refresh
        return redirect()->route('filament.kelola.pages.dashboard');
    }

    public function render()
    {
        return view('livewire.tahun-anggaran-switcher', [
            'tahunList' => $this->tahunList,
            'selectedTahun' => $this->selectedTahun,
        ]);
    }
}
