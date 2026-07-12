<?php

namespace App\Filament\Widgets;

use App\Models\PaguTahunAnggaran;
use Filament\Widgets\Widget;

class TahunAnggaranSwitcherWidget extends Widget
{
    protected string $view = 'filament.widgets.tahun-anggaran-switcher';
    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';

    public $selectedTahun = null;
    public $tahunList = [];
    public $isActiveTahun = true;

    public function mount(): void
    {
        $this->tahunList = PaguTahunAnggaran::orderByDesc('tahun')
            ->pluck('tahun', 'id')
            ->toArray();

        $activeTahun = PaguTahunAnggaran::where('status_aktif', 1)->value('tahun');

        $this->selectedTahun = session('selected_tahun_anggaran') ?? $activeTahun;
        $this->isActiveTahun = $this->selectedTahun == $activeTahun;
    }

    public function switchTahun($tahun)
    {
        session(['selected_tahun_anggaran' => $tahun]);

        return redirect()->route('filament.kelola.pages.dashboard');
    }
}
